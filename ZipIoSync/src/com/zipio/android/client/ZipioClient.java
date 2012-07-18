package com.zipio.android.client;

import java.io.BufferedReader;
import java.io.File;
import java.io.IOException;
import java.io.InputStreamReader;
import java.io.UnsupportedEncodingException;
import java.lang.reflect.Type;
import java.net.URI;
import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;
import java.util.ArrayList;
import java.util.Collection;
import java.util.List;

import org.apache.http.HttpEntity;
import org.apache.http.HttpResponse;
import org.apache.http.client.ClientProtocolException;
import org.apache.http.client.HttpClient;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.entity.mime.MultipartEntity;
import org.apache.http.entity.mime.content.ContentBody;
import org.apache.http.entity.mime.content.FileBody;
import org.apache.http.entity.mime.content.StringBody;
import org.apache.http.impl.client.DefaultHttpClient;

import android.app.Activity;
import android.database.Cursor;
import android.net.Uri;
import android.provider.MediaStore;

import com.google.gson.Gson;
import com.google.gson.GsonBuilder;
import com.google.gson.JsonSyntaxException;
import com.google.gson.reflect.TypeToken;

public class ZipioClient {
  private final String HOST = 
      "http://zipio.com";
  private final HttpClient client;
  private final Activity activity;
  private String email;
  private String password;

  public ZipioClient(Activity activity) {
    this.activity = activity;
    client = new DefaultHttpClient();
  }

  public void setCredentials(String email, String password) {
    this.email = email;
    this.password = password;
  }

  public String getEmail() {
    return email;
  }

  public String getPassword() {
    return password;
  }

  public List<Album> getAlbums() throws IOException {
    if (email == null || email.length() == 0 ||
        password == null || password.length() == 0) {
      return new ArrayList<Album>();
    }
    HttpPost post = new HttpPost();
    post.setURI(URI.create(
        String.format("%s/get_albums_info.php?email=%s&password_hash=%s",
            HOST, email, computeSha1(password))));
    System.err.println("post: " + post.getRequestLine());
    HttpResponse response = client.execute(post);
    HttpEntity resEntity = response.getEntity();
    Gson gson = new GsonBuilder().create(); 
    Type collectionType = new TypeToken<Collection<Album>>(){}.getType();
    System.err.println("Status code: " + 
        response.getStatusLine().getStatusCode() +
        " " + resEntity.getContentLength());
    if (response.getStatusLine().getStatusCode() != 200) {
      System.err.println("Status code: " + response.getStatusLine().getStatusCode());
      return new ArrayList<Album>();       
    }
    String text = getResponseText(resEntity);
    try {
      Collection<Album> albums = 
          gson.fromJson(text, collectionType);
      return new ArrayList<Album>(albums);
    } catch (JsonSyntaxException e) {
      e.printStackTrace();
      return new ArrayList<Album>();
    }
  }

  private String getResponseText(HttpEntity resEntity)
      throws UnsupportedEncodingException, IOException {
    BufferedReader content = 
        new BufferedReader(new InputStreamReader(resEntity.getContent(), "UTF-8"));
    StringBuilder text = new StringBuilder();
    for (String line = content.readLine(); line != null; line = content.readLine()) {
      text.append(line);
      text.append('\n');
    }
    System.err.println("response: \'" + text + "\'");
    return text.toString();
  }

  public boolean uploadPhoto(Uri photo, String album, int albumId)
      throws IOException {
    String[] filePathColumn = {MediaStore.Images.Media.DATA};
    Cursor cursor = activity.getContentResolver().
        query(photo, filePathColumn, null, null, null);
    cursor.moveToFirst();

    int columnIndex = cursor.getColumnIndex(filePathColumn[0]);
    String filePath = cursor.getString(columnIndex);
    System.err.println("File Path: " + filePath);
    cursor.close();
    File file = new File(filePath);
    return uploadPhoto(file, albumId);
  }

  public boolean uploadPhoto(File file, int albumId)
      throws UnsupportedEncodingException, IOException, ClientProtocolException {
    ContentBody cbFile = new FileBody(file, "image/jpeg");
    HttpPost post = new HttpPost();
    post.setURI(URI.create(
        String.format("%s/post_photo.php", HOST)));
    System.err.println("URI: " + post.getURI());
    MultipartEntity mpEntity = new MultipartEntity();
    mpEntity.addPart("email", new StringBody("" + email));
    mpEntity.addPart("password_hash", new StringBody(computeSha1(password)));
    mpEntity.addPart("album_id", new StringBody("" + albumId));
    mpEntity.addPart("photo", cbFile);
    post.setEntity(mpEntity);
    HttpResponse response = client.execute(post);
    System.err.println("Status: " + response.getStatusLine().getStatusCode());
    String responseText = getResponseText(response.getEntity());
    return (response.getStatusLine().getStatusCode() == 200) && 
        (responseText.length() == 2);
  }

  public static String computeSha1(String text)  {
    try {
      MessageDigest md = MessageDigest.getInstance("SHA-1");        
      md.update(text.getBytes("iso-8859-1"), 0, text.length());
      byte[] sha1hash = md.digest();
      return bytesToHex(sha1hash);
    } catch (UnsupportedEncodingException e) {
      throw new RuntimeException(e);
    } catch (NoSuchAlgorithmException e) {
      e.printStackTrace();
      throw new RuntimeException(e);
    }
  } 

  public static String bytesToHex(byte[] data) {
    StringBuffer buf = new StringBuffer();
    for (int i = 0; i < data.length; i++) {
      buf.append(byteToHex(data[i]));
    }
    return (buf.toString());
  }

  private static String byteToHex(byte data) {
    StringBuffer buf = new StringBuffer();
    buf.append(toHexChar((data >>> 4) & 0x0F));
    buf.append(toHexChar(data & 0x0F));
    return buf.toString();
  }

  private static char toHexChar(int i) {
    if ((0 <= i) && (i <= 9)) {
      return (char) ('0' + i);
    } else {
      return (char) ('a' + (i - 10));
    }
  }

  public static class Album {
    private String handle;
    private int user_id;
    private int id;
    
    public String getHandle() {
      return handle;
    }

    public int getUser_id() {
      return user_id;
    }

    public int getId() {
      return id;
    }


    @Override
    public String toString() {
      return "Album [handle=" + handle + ", user_id=" + user_id + ", id=" + id
          + "]";
    }
  }
}

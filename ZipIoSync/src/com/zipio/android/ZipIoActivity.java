package com.zipio.android;

import java.io.IOException;
import java.util.List;

import android.app.Activity;
import android.content.Intent;
import android.content.SharedPreferences;
import android.database.Cursor;
import android.graphics.Bitmap;
import android.net.Uri;
import android.os.Bundle;
import android.provider.MediaStore;
import android.view.KeyEvent;
import android.view.Menu;
import android.view.View;
import android.widget.Button;
import android.widget.ImageView;
import android.widget.RadioButton;
import android.widget.RadioGroup;
import android.widget.TextView;
import android.widget.Toast;

import com.zipio.android.client.ZipioClient;
import com.zipio.android.client.ZipioClient.Album;

public class ZipIoActivity extends Activity {
  private static final int NEW_ALBUM_ID = -1;
  private static final String PREF_NAME = "ZipioPreferences";
  private static final String SAVED_EMAIL = "email";
  private static final String SAVED_PASSWORD = "password";
   
  private ZipioClient client;
  private Uri imageUri;
  private List<Album> albums;
  
  @Override
  public void onCreate(Bundle savedInstanceState) {
    super.onCreate(savedInstanceState);
    client = new ZipioClient(this);
    setContentView(R.layout.activity_zip_io);
    final RadioGroup choices = (RadioGroup) findViewById(R.id.newOrAlbumRadioGroup);
    choices.setVisibility(View.INVISIBLE);  
    setupPhoto();
    updateCredentials(true);
    updateAlbums(choices);
    TextView.OnEditorActionListener onEditorActionListener = new TextView.OnEditorActionListener() {
      @Override
      public boolean onEditorAction(TextView v, int actionId, KeyEvent event) {
        System.err.println("action: " + actionId);
        updateCredentials(false);
        updateAlbums(choices);
        return false;
      }
    };
    getEmailForm().setOnEditorActionListener(onEditorActionListener);
    getPasswordForm().setOnEditorActionListener(onEditorActionListener);
    Button upload = (Button) findViewById(R.id.uploadButton);
    if (imageUri != null) {
      upload.setOnClickListener(new View.OnClickListener() {
        @Override
        public void onClick(View v) {
          Thread albumThread = new Thread(new Runnable() {
            @Override
            public void run() {
              upload(); 
            }
          });
          albumThread.start();
        }
      });
    }
  }

  @Override
  public void onPause() {
    super.onPause();
    System.err.println("OnPause");
    String email = client.getEmail();
    if (email != null && email.length() > 0) {
      SharedPreferences.Editor preferences = 
          getBaseContext().getSharedPreferences(PREF_NAME, MODE_PRIVATE).edit();
      preferences.putString(SAVED_EMAIL,  email);
  
      System.err.println("Save state: " + client.getEmail());
      preferences.putString(SAVED_PASSWORD, client.getPassword());
      preferences.commit();
    }
  }
  
  private void upload() {
    RadioGroup choices = (RadioGroup) findViewById(R.id.newOrAlbumRadioGroup);
    RadioButton button = (RadioButton) findViewById(choices.getCheckedRadioButtonId());
    final String albumName = "" + button.getText();
    Album selected = null;
    for (Album album : albums) {
      if (album.getHandle().equals(albumName)) {
        selected = album;
        break;
      }
    }
    System.err.println("Selected album: " + albumName + " " + selected);
    try {
      if (selected != null) {
        final boolean success = 
            client.uploadPhoto(imageUri, albumName, selected.getId());
        runOnUiThread(new Runnable() {
          @Override
          public void run() {
            if (success) {
              Toast.makeText(getBaseContext(), 
                  "Photo uploaded to " + albumName, Toast.LENGTH_SHORT).show();
            } else {
              Toast.makeText(getBaseContext(), 
                  "Photo uploaded failed!", Toast.LENGTH_SHORT).show();    
            }
          }          
        });
      }
    } catch (IOException e) {
      // TODO Auto-generated catch block
      e.printStackTrace();
    }
  }
  
  private void updateAlbums(RadioGroup choices) {
    choices.removeAllViews();
    Thread albumThread = new Thread(new Runnable() {
      @Override
      public void run() {
        try {
          albums = client.getAlbums();
          runOnUiThread(new Runnable() {
            @Override
            public void run() {
              RadioGroup choices = (RadioGroup) findViewById(R.id.newOrAlbumRadioGroup);
              choices.setVisibility(View.VISIBLE);
              RadioButton button = new RadioButton(ZipIoActivity.this);
              button.setText("New Album");
              button.setId(NEW_ALBUM_ID);
              button.setSelected(true);
              choices.addView(button);
              for (Album album : albums) {
                button = new RadioButton(ZipIoActivity.this);
                button.setText(album.getHandle());
                choices.addView(button);
              }     
            }         
          });
        }
        catch (IOException e) {
          // TODO Auto-generated catch block
          e.printStackTrace();
        }
      }
    });
    albumThread.start();
  }

  private void updateCredentials(boolean readFromPreferences) {
    if (readFromPreferences) {
      SharedPreferences preferences = 
          getBaseContext().getSharedPreferences(PREF_NAME, MODE_PRIVATE);
      String email = preferences.getString(SAVED_EMAIL, null);
      System.err.println("Email from saved bundle: " + email);
      if (email != null) {
        getEmailForm().setText(email);
      }
      String password = preferences.getString(SAVED_PASSWORD, null);
      if (password != null) {
        getPasswordForm().setText(password);
      }
    }
    client.setCredentials("" + getEmailForm().getText(), 
        "" + getPasswordForm().getText());
  }

  private TextView getPasswordForm() {
    return (TextView) findViewById(R.id.passwordForm);
  }

  private TextView getEmailForm() {
    return (TextView) findViewById(R.id.emailForm);
  }

  private boolean setupPhoto() {
    Intent intent = getIntent();
    imageUri = (Uri) intent.getParcelableExtra(Intent.EXTRA_STREAM);
    if (imageUri == null) {
      return false;
    }
    System.err.println("Image URI: " + imageUri);
    String[] filePathColumn = {MediaStore.Images.Media._ID};
                    
    Cursor cursor = getContentResolver().
        query(imageUri, filePathColumn, null, null, null);
    cursor.moveToFirst();
    int columnIndex = cursor.getColumnIndex(filePathColumn[0]);
    long id = cursor.getInt(columnIndex);
    System.err.println("Thumbnail: " + id);
    Bitmap bitmap =  MediaStore.Images.Thumbnails.getThumbnail(
        getContentResolver(), id, 0, MediaStore.Images.Thumbnails.MINI_KIND, null);
    ImageView view = (ImageView) findViewById(R.id.imagePreview);
   // view.setImageURI(Uri.fromFile(new File(filePath)));
    view.setImageBitmap(bitmap);
    cursor.close();
    return true;
  }

  @Override
  public boolean onCreateOptionsMenu(Menu menu) {
    getMenuInflater().inflate(R.menu.activity_zip_io, menu);
    return true;
  }
}

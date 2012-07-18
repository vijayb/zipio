package com.zipio.android;

import java.io.IOException;
import java.util.List;

import android.app.Activity;
import android.app.Dialog;
import android.content.Intent;
import android.database.Cursor;
import android.graphics.Bitmap;
import android.net.Uri;
import android.os.Bundle;
import android.provider.MediaStore;
import android.view.Menu;
import android.view.View;
import android.widget.Button;
import android.widget.ImageView;
import android.widget.RadioButton;
import android.widget.RadioGroup;
import android.widget.Toast;

import com.zipio.android.client.ZipioClient;
import com.zipio.android.client.ZipioClient.Album;

public class ZipIoActivity extends Activity {
  private static final int NEW_ALBUM_ID = -1;
   
  private ZipioClient client;
  private LoginScreen loginScreen;
  private CaptureScreen captureScreen;
  private Uri imageUri;
  private List<Album> albums;
  
  @Override
  public void onCreate(Bundle savedInstanceState) {
    super.onCreate(savedInstanceState);
    client = new ZipioClient(this);
    loginScreen = new LoginScreen(this, client);
    captureScreen = new CaptureScreen(this, client);
    loginScreen.init();
    if (!loginScreen.isShowing()) {
      loginComplete();
    }
    
    if (imageUri != null) {
      final Button upload = (Button) findViewById(R.id.uploadButton);
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
  protected void onResume() {
    super.onResume();
    captureScreen.onResume();
  }


  @Override
  public void onPause() {
    super.onPause();
    System.err.println("OnPause");
    captureScreen.onPause();
    loginScreen.onPause(); 
  }
  
  @Override
  @Deprecated
  protected Dialog onCreateDialog(int id, Bundle bundle) {
    if (id == LoginScreen.ACCOUNT_DIALOG) {
      return loginScreen.createAccountDialog();
    }
    return null;
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
  
  public void updateAlbums() {
    Thread albumThread = new Thread(new Runnable() {
      @Override
      public void run() {
        try {
          albums = client.getAlbums();
          runOnUiThread(new Runnable() {
            @Override
            public void run() {
              setContentView(R.layout.activity_zip_io);
              final RadioGroup choices =
                  (RadioGroup) findViewById(R.id.newOrAlbumRadioGroup);
              setupPhoto();
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
  
  public void loginComplete() {
    captureScreen.showCaptureScreen();
  }

//  @Override
//  public View onCreateView(String name, Context context, AttributeSet attrs) {
//    // TODO Auto-generated method stub
//    System.err.println("Creating: " + name  + " " + CameraLayout.class.getName());
//    View view = super.onCreateView(name, context, attrs);
//    if (CameraLayout.class.getName().equals(name)) {
//      //  captureScreen.setup();
//      System.err.println("Got Custom View");
//    } else if (view != null) {
//      System.err.println("View: " + view.getClass());
//    }
//    return view;
//  }
}

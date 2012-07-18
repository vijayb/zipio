package com.zipio.android;

import java.io.File;
import java.io.FileOutputStream;
import java.io.IOException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;
import java.util.List;

import android.app.Activity;
import android.hardware.Camera;
import android.hardware.Camera.Parameters;
import android.hardware.Camera.PictureCallback;
import android.hardware.Camera.Size;
import android.media.ExifInterface;
import android.media.MediaPlayer;
import android.media.MediaPlayer.OnCompletionListener;
import android.os.Environment;
import android.util.Log;
import android.view.Display;
import android.view.Surface;
import android.view.SurfaceHolder;
import android.view.SurfaceView;
import android.view.View;
import android.view.WindowManager;
import android.widget.ArrayAdapter;
import android.widget.Button;
import android.widget.LinearLayout;
import android.widget.Spinner;
import android.widget.Toast;

import com.zipio.android.client.ZipioClient;
import com.zipio.android.client.ZipioClient.Album;

public class CaptureScreen {
  private final ZipIoActivity activity;
  private Camera camera;
  private ZipioClient client;

  private List<Album> albums;

  boolean displayed = false;
  boolean paused = false;
  
  public CaptureScreen(ZipIoActivity zipIoActivity, ZipioClient client) {
    this.activity = zipIoActivity;
    this.client = client;
    camera = Camera.open();
  }

  public void showCaptureScreen() {
    displayed = true;
    activity.setContentView(R.layout.camera_screen);
    setupScreen();
  }

  private void setupScreen() {
    if (!displayed) {
      return;
    }
    activity.runOnUiThread(new Runnable() {
      @Override
      public void run() {
        setupCamera();
      }
    });
    
    new Thread(new Runnable() {
      @Override
      public void run() {
        try {
          if (albums == null) {
            albums = client.getAlbums();
          }
          activity.runOnUiThread(new Runnable() {
            @Override
            public void run() {
              addAlbumsToSpinner();
            }
          });
        } catch (IOException e) {
          // TODO Auto-generated catch block
          e.printStackTrace();
        }
      }
    }).start();
    
  }

  private void addAlbumsToSpinner() {
    Spinner spinner = (Spinner) activity.findViewById(R.id.album_spinner);
    if (spinner == null) {
      System.err.println("No spinner");
      return;
    }
    List<String> albumNames = new ArrayList<String>();
    for (int i = 0; i < albums.size(); i++) {
      albumNames.add(albums.get(i).getHandle());
    }
    ArrayAdapter<String> dataAdapter = new ArrayAdapter<String>(activity,
        android.R.layout.simple_spinner_item, albumNames);
    dataAdapter.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item);
    spinner.setAdapter(dataAdapter);
  }

  private void setupCamera() {
    System.err.println("setting up camera");
    final Button button = (Button) activity.findViewById(R.id.snap_picture);
    button.setOnClickListener(new View.OnClickListener() {
      @Override
      public void onClick(View v) {
        MediaPlayer mp = MediaPlayer.create(activity, R.raw.camera_click);   
        mp.setOnCompletionListener(new OnCompletionListener() {
            @Override
            public void onCompletion(MediaPlayer mp) {
                // TODO Auto-generated method stub
                mp.release();
            }
        });
        mp.start();
        takePicture();
      }
    });
    final SurfaceView surface =
        (SurfaceView) activity.findViewById(R.id.camera_preview);
    final SurfaceHolder surfaceHolder = surface.getHolder();
    surfaceHolder.addCallback(new SurfaceHolder.Callback() {
      @Override
      public void surfaceChanged(SurfaceHolder holder, int format, int width, int height) {            
        if (camera == null) {
          camera = Camera.open();
        } else {
          camera.stopPreview();
        }

        if (setSurfaceSize(surface, width, height)) {
          return;
        }

        Parameters parameters = camera.getParameters();
        List<Size> sizes = parameters.getSupportedPictureSizes();
        Size maxSize = sizes.get(0);
        for (Size size : sizes) {
          if (size.height * size.width > maxSize.height * maxSize.width) {
            maxSize = size;
          }
        }
        System.err.println("Size: " + maxSize.width + ", " + maxSize.height);
        parameters.setPictureSize(maxSize.width, maxSize.height);   
        Display display = 
            ((WindowManager) activity.getSystemService(Activity.WINDOW_SERVICE))
            .getDefaultDisplay();
        switch (display.getRotation()) {
        case Surface.ROTATION_0:
          camera.setDisplayOrientation(90);
          break;
        case Surface.ROTATION_90:
          break;
        case Surface.ROTATION_180:
          break;
        case Surface.ROTATION_270:
          camera.setDisplayOrientation(0);
          break;
        }

        camera.setParameters(parameters);   
        System.err.println("Attaching to camera");
        try {
          camera.setPreviewDisplay(surfaceHolder);
          camera.startPreview();
        } catch (IOException e) {
          // TODO Auto-generated catch block
          e.printStackTrace();
        }
      }

      @Override
      public void surfaceCreated(SurfaceHolder holder) {
      }

      @Override
      public void surfaceDestroyed(SurfaceHolder holder) {
      }
    });
  }
  
  protected void takePicture() {
    PictureCallback picture = new PictureCallback() {
      @Override
      public void onPictureTaken(byte[] data, Camera camera) {
        final SurfaceView surface =
            (SurfaceView) activity.findViewById(R.id.camera_preview);
        final SurfaceHolder surfaceHolder = surface.getHolder();
        try {
          camera.setPreviewDisplay(surfaceHolder);
        } catch (IOException e1) {
          // TODO Auto-generated catch block
          e1.printStackTrace();
        }
        camera.startPreview();

        final File pictureFile = getOutputMediaFile();
        if (pictureFile == null){
          Log.i("blah", "Error creating media file, check storage permissions");
          return;
        }

        try {
          System.err.println("Taking Picture");
          FileOutputStream fos = new FileOutputStream(pictureFile);
          fos.write(data);
          fos.close();
          
          ExifInterface exif = new ExifInterface(pictureFile.getAbsolutePath());
          int orientation = 
              exif.getAttributeInt(ExifInterface.TAG_ORIENTATION, 
                  ExifInterface.ORIENTATION_NORMAL);
          Display display = 
              ((WindowManager) activity.getSystemService(Activity.WINDOW_SERVICE))
              .getDefaultDisplay();
          switch (display.getRotation()) {
          case Surface.ROTATION_0:
            if (orientation != ExifInterface.ORIENTATION_NORMAL) {
              exif.setAttribute(ExifInterface.TAG_ORIENTATION,
                  "" + ExifInterface.ORIENTATION_ROTATE_90);
              exif.saveAttributes();
            }
            break;
          case Surface.ROTATION_90:
            break;
          case Surface.ROTATION_180:
            break;
          case Surface.ROTATION_270:
            break;
          }

          System.err.println("Orientation: " + orientation);
          
          new Thread(new Runnable() {
            @Override
            public void run() {
              uploadPicture(pictureFile);
            }
          }).start();
        } catch (IOException e) {
          e.printStackTrace();
        }
      }
    };
    System.err.println("about to call takePicture");
    camera.takePicture(null, null, picture);
  }

  /** Create a File for saving an image or video */
  private File getOutputMediaFile(){
    // To be safe, you should check that the SDCard is mounted
    // using Environment.getExternalStorageState() before doing this.

    File mediaStorageDir = new File(Environment.getExternalStoragePublicDirectory(
        Environment.DIRECTORY_PICTURES), "ZipIo");


    // This location works best if you want the created images to be shared
    // between applications and persist after your app has been uninstalled.

    // Create the storage directory if it does not exist
    try {
      if (! mediaStorageDir.exists()) {
        System.err.println("mediaStorageDir does not exist: " +
            mediaStorageDir.getCanonicalPath());
        if (! mediaStorageDir.mkdirs()) {
          System.err.println("Cannot create: " + mediaStorageDir.getCanonicalPath());
          return null;
        }
      }
    } catch (IOException e) {
      // TODO Auto-generated catch block
      e.printStackTrace();
    }

    // Create a media file name
    String timeStamp = new SimpleDateFormat("yyyyMMdd_HHmmss").format(new Date());
    File mediaFile;
      mediaFile = new File(mediaStorageDir.getPath() + File.separator +
          "Zipio_"+ timeStamp + ".jpg");

    return mediaFile;
  }
  
  private boolean setSurfaceSize(SurfaceView surface, int width, int height) {
    if (camera == null) {
      return false;
    }
    Size size = camera.getParameters().getPictureSize();
    double ratio = size.height / (double) size.width;
    Display display =
        ((WindowManager) activity.getSystemService(Activity.WINDOW_SERVICE))
        .getDefaultDisplay();
    switch (display.getRotation()) {
    case Surface.ROTATION_0:
    case Surface.ROTATION_180:
      int newHeight = (int) (width / ratio);
      if (newHeight != height) {
        System.err.println("Updating height: " + height + " " + newHeight);
        surface.setLayoutParams(
            new LinearLayout.LayoutParams(
                width, newHeight));
        return true;
      }
      return false;
    case Surface.ROTATION_90:
    case Surface.ROTATION_270:
      int newWidth = (int) (height / ratio);
      if (newWidth != width) {
        System.err.println("Updating width: " + width + " " + newWidth);
        surface.setLayoutParams(
            new LinearLayout.LayoutParams(
                newWidth, height));
        return true;
      }
      return false;
    }
    return false;
  }

  public void onPause() {
    System.err.println("Pausing!");
    camera.release();
    paused = true;
  }

  public void onResume() {
    System.err.println("Resuming!");
    if (displayed && paused) {
      System.err.println("Getting new camera");
      camera = Camera.open();
      paused = false;
      activity.setContentView(R.layout.camera_screen);
      setupCamera();
    }
  }

  private void uploadPicture(final File pictureFile) {
    Spinner spinner = (Spinner) activity.findViewById(R.id.album_spinner);
    if (spinner == null) {
      System.err.println("No spinner");
      return;
    }
    final int id = (int) spinner.getSelectedItemId();
    if (albums.size() == 0) {
      Toast.makeText(activity.getBaseContext(), "No Album selected", Toast.LENGTH_SHORT).show();
      return;
    }
    
    try {
      final boolean success = client.uploadPhoto(
          pictureFile, albums.get(id).getId());
      activity.runOnUiThread(new Runnable() {
        @Override
        public void run() {
          Toast.makeText(activity.getBaseContext(), 
              success ? "Photo uploaded to " + albums.get(id).getHandle()
                  : "Error uploading photo", Toast.LENGTH_SHORT).show();
        }
      });
    } catch (IOException e) {
      // TODO Auto-generated catch block
      e.printStackTrace();
    }
  }

  public void setup() {
    setupScreen();
  }
}

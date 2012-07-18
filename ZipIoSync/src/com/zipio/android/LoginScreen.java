package com.zipio.android;

import java.io.IOException;
import java.util.ArrayList;
import java.util.List;
import java.util.regex.Pattern;

import android.accounts.Account;
import android.accounts.AccountManager;
import android.app.Activity;
import android.app.AlertDialog;
import android.app.Dialog;
import android.content.DialogInterface;
import android.content.SharedPreferences;
import android.os.Handler;
import android.os.SystemClock;
import android.util.Patterns;
import android.view.KeyEvent;
import android.view.MotionEvent;
import android.view.View;
import android.widget.EditText;
import android.widget.TextView;
import android.widget.Toast;

import com.zipio.android.client.ZipioClient;
import com.zipio.android.client.ZipioClient.Album;

public class LoginScreen {
  private static final String PREF_NAME = "ZipioPreferences8";
  private static final String SAVED_EMAIL = "email";
  private static final String SAVED_PASSWORD = "password";
  public static final int ACCOUNT_DIALOG = 0;

  private final ZipIoActivity activity;
  private final ZipioClient client;
  
  private List<String> emails;  
  private String email;
  private String password;
  private boolean authenticated = false;
  
  public LoginScreen(ZipIoActivity activity, ZipioClient client) {
    this.activity = activity;
    this.client = client;
  }
  
  public void init() {
    readCredentials();
    if (!authenticated) {
      showLoginScreen();
      System.err.println("Get email: " + email);

      if (email == null) {
        getEmailFromAccount();
      } else {
        updateEmail(email);
      }
    }
  }

  private EditText getPasswordForm() {
    return (EditText) activity.findViewById(R.id.password);
  }

  private EditText getEmailForm() {
    return (EditText) activity.findViewById(R.id.email);
  }

  private void showLoginScreen() {
    activity.setContentView(R.layout.login_screen);
    activity.findViewById(R.id.loginProgress).
      setVisibility(View.INVISIBLE);

    TextView.OnEditorActionListener onEditorActionListener = new TextView.OnEditorActionListener() {
      @Override
      public boolean onEditorAction(TextView v, int actionId, KeyEvent event) {
        String password = "" + getPasswordForm().getText();
        if (!password.isEmpty()) {
          LoginScreen.this.password = password;
          updateAlbums();
        }
        return false;
      }
      
    };
    getEmailForm().setOnEditorActionListener(onEditorActionListener);
    getPasswordForm().setOnEditorActionListener(onEditorActionListener);
  }

  private void updateAlbums() {
    activity.findViewById(R.id.loginProgress).
      setVisibility(View.VISIBLE);
    client.setCredentials(email, password);
    new Thread(new Runnable() {
      @Override
      public void run() {
        try {
          List<Album> albums = client.getAlbums();
          if (albums == null) {
            Toast.makeText(
                activity.getBaseContext(), "Login Failed", Toast.LENGTH_SHORT).show();
          } else {
            activity.loginComplete();
          }
        } catch (IOException e) {
          e.printStackTrace();
        }
      }
    }).start();
  }
  
  public boolean isShowing() {
    return !authenticated;
  }

  private void readCredentials() {
    SharedPreferences preferences = 
        activity.getBaseContext().getSharedPreferences(
            PREF_NAME, Activity.MODE_PRIVATE);
    email = preferences.getString(SAVED_EMAIL, null);
    System.err.println("Email from saved bundle: " + email);
    if (email != null) {
      password = preferences.getString(SAVED_PASSWORD, null);
      if (password != null) {
        authenticated = true;
        client.setCredentials(email,  password);
      }
    }
  }
  
  private void getEmailFromAccount() {
    System.err.println("Get email from account");
    Pattern emailPattern = Patterns.EMAIL_ADDRESS; // API level 8+
    Account[] accounts = AccountManager.get(activity).getAccounts();
    emails = new ArrayList<String>();
    for (int i = 0; i < accounts.length; i++) {
      if (emailPattern.matcher(accounts[i].name).matches() &&
          !emails.contains(accounts[i].name)) {
        emails.add(accounts[i].name);
      }
    }
    emails.add("Other");
    activity.showDialog(ACCOUNT_DIALOG);
  }
  
  public Dialog createAccountDialog() {
  if (emails == null) {
      System.err.println("Emails are null!");
      return null;
    }
    AlertDialog.Builder dialog = new AlertDialog.Builder(activity);
    System.err.println("Setting up PW");
    dialog.setTitle("Pick Email Account");
    dialog.setItems(emails.toArray(new String[emails.size()]),
        new DialogInterface.OnClickListener() {
      @Override
      public void onClick(DialogInterface dialog, int which) {
        if (which == emails.size() - 1) {
          updateEmail(null);
        } else {
          updateEmail(emails.get(which));
        }
        dialog.dismiss();
      }
    });
    
    return dialog.create();
  }
  
  public void onPause() {
    if (email != null && email.length() > 0) {
      SharedPreferences.Editor preferences = 
          activity.getBaseContext().getSharedPreferences(
              PREF_NAME, Activity.MODE_PRIVATE).edit();
      preferences.putString(SAVED_EMAIL,  email);
  
      System.err.println("Save state: " + email);
      preferences.putString(SAVED_PASSWORD, password);
      preferences.commit();
    }
  }

  public void updateEmail(String email) {
    this.email = email;
    getEmailForm().setText(email);
    final EditText view = (email == null) ? getEmailForm() : getPasswordForm();
    view.requestFocus();
    (new Handler()).postDelayed(new Runnable() {
      public void run() {
        view.dispatchTouchEvent(MotionEvent.obtain(SystemClock.uptimeMillis(), SystemClock.uptimeMillis(), MotionEvent.ACTION_DOWN , 0, 0, 0));
        view.dispatchTouchEvent(MotionEvent.obtain(SystemClock.uptimeMillis(), SystemClock.uptimeMillis(), MotionEvent.ACTION_UP , 0, 0, 0));
      }
  }, 100);
//    InputMethodManager imm = (InputMethodManager)
//       activity.getSystemService(Context.INPUT_METHOD_SERVICE);
//    imm.showSoftInput(view, InputMethodManager.SHOW_FORCED);
  }
}

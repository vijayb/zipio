package com.zipio.android.client;

import java.io.IOException;
import java.util.List;

import junit.framework.TestCase;

import com.zipio.android.client.ZipioClient.Album;

public class ZipioClientTest extends TestCase {

  public void testGetAlbums() throws IOException {
    ZipioClient client = new ZipioClient(null);
    client.setCredentials("josh.sacks@gmail.com", "sanjay");
   List<Album> albums = client.getAlbums();
   System.err.println("Albums: " + albums);
   assertEquals(2, albums.size());    
  }
}


<form action="process_email.php" method="post" enctype="multipart/form-data">

<table>

<tr>
    <td>file</td>
    <td><input name="attachment-1" type="file"/></td>
</tr>

<tr>
    <td>recipient</td>
    <td><input name="recipient" type="text" value="vacation@zipiyo.com"/></td>
</tr>

<tr>
    <td>sender</td>
    <td><input name="sender" type="text" value="sanjay@gmail.com"/></td>
</tr>

<tr>
    <td>subject</td>
    <td><input name="subject" type="text" value="test"/></td>
</tr>

<tr>
    <td>from</td>
    <td><input name="from" type="text" value="Sanjay G. Mavinkurve"/></td>
</tr>

<tr>
    <td>attachment-count</td>
    <td><input name="attachment-count" type="text" value="1"/></td>
</tr>

<tr>
    <td></td>
    <td><input type="submit" value="Send post"/></td>
</tr>

</table>

</form>

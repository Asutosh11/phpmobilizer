### Installation ###

  1. Create a subdomain on your server called 'm'. (Ex: m.example.com)
    * Depending on what kind of hosting you have, the procedure to do this may be either editing configuration files and restarting the server, accessing a control panel, or having a system administrator set it up.
  1. Upload the files from the 'm' directory to the document root for your new subdomain.
  1. Your mobile site is working! Just visit your m subdomain to see it.

<br>


<h3>Setting up auto redirect</h3>

If your website is in PHP, you can use phpMobilizer to set up automatic redirection to your mobile site for mobile devices.<br>
<br>
<ol><li>First you will need to determine where to place the redirect code. Usually you would want to place it in a common include file, such as a config file, a database connection file or a header file.<br>
</li><li>Add the following line of code before any html or text output.<br>
<ul><li><code>require_once('/home/path/to/subdomain/m/mobilize.php');</code>
</li><li>Edit the path so it is pointing directly to the mobilize.php file, which will be in the subdomain's document root.<br>
</li></ul></li><li>Visit your website with a mobile device and verify it redirects you!
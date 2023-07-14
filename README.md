<h3>GitHub Project Viewer</h3>
<hr />
This project viewer allows a simple way to pull, store, and display the most starred projects on GitHub for any given coding language. By default, the system is set to "php".
<br /><br />
<b>REQUIREMENTS</b><br />
<ul>
 <li>A Webserver environment such as Apache</li>
 <li>Recent version of PHP ( 7+ )</li>
 <li>MySQL Server</li>
</ul>

<b>GETTING STARTED</b><br />
<ol>
 <li>Download the project files and place them into your webserver document root</li>
 <li>Navigate to the "/api" directory</li>
 <li>Edit the "connection.php" file so that it contains the correct MySQL details for host, user, and password</li>
 <li>Edit the "settings.php" file for the desired language. ( Optionally you can add your GitHub username as their API doc asks for it )</li>
 <li>Visit "localhost" or "127.0.0.1" on your web browser</li>
</ol>

<b>NOTES</b><br />
The system will automatically create the required schema for you. You do not need to create a database or table yourself. If you accidentally delete the schema after the system builds it for you,
it is easy to regenerate it. Simply navigate to "/api/classes" in the project files and edit "createschema.txt" so that the file contains the number 1. When you refresh your browser, the app will
rebuild the schema for you. 
<br />
It is </b>HIGHLY</b> recommended that you move "connection.php" outside of your document root for security. If you do, navigate to "/api" in your project files and edit "index.php". In
the section "SETTINGS", be sure to update the "connection.php" file path to the location where you moved the file to. 

If you're working on a server and you're logged in as `root` (as shown by `root@ubuntu-s-1vcpu-1gb-sgp1-01:/`), you have full administrative access to the system. Here's how you can follow the process for creating a user account, assigning it to a group, granting permission access, and setting up the web server.

### 1. **Create a New User**
Since you're logged in as `root`, you can create a new user by simply running the following command:

```bash
adduser username
```

Replace `username` with the desired username. This will create the user and prompt you for a password and other information.

### 2. **Create a User Group**
If you want to create a new group to assign the user to, run:

```bash
addgroup groupname
```

Replace `groupname` with your desired group name.

### 3. **Assign User to Group**
Now, you can add the newly created user to the group using the `usermod` command:

```bash
usermod -aG groupname username
```

Replace `groupname` with the name of the group, and `username` with the name of the user you created.

### 4. **Grant Permissions to Web Server Files**
Now, set the necessary permissions on the web server's root directory (for Apache or Nginx).

#### For Apache (default document root is `/var/www/ezzeteam`):
```bash
chown -R username:groupname /var/www/ezzeteam
chmod -R 755 /var/www/ezzeteam
```

#### For Nginx (if the document root is `/usr/share/nginx/ezzeteam` or `/var/www/ezzeteam`):
```bash
chown -R username:groupname /var/www/ezzeteam
chmod -R 755 /var/www/ezzeteam
```

This ensures that the user has permission to read, write, and execute files within the web root.

### 5. **Grant User Sudo Access (Optional)**
If you want the user to have `sudo` access (for example, to restart the web server or manage the system), add the user to the `sudo` group:

```bash
usermod -aG sudo username
```

This will allow the user to execute administrative commands with `sudo`.

To give the user specific permissions to manage the web server without full `sudo` access, you can edit the `sudoers` file:

```bash
visudo
```

Add the following line to grant them permission to restart the web server (Apache in this case):

```bash
username ALL=(ALL) NOPASSWD: /bin/systemctl restart apache2
```

### 6. **Install Web Server**

#### For Apache:
```bash
apt update
apt install apache2
```

#### For Nginx:
```bash
apt update
apt install nginx
```

### 7. **Enable and Start the Web Server**

#### For Apache:
```bash
systemctl enable apache2
systemctl start apache2
```

#### For Nginx:
```bash
systemctl enable nginx
systemctl start nginx
```

### 8. **Testing the Web Server**
You can test whether the web server is working by using `curl`:

```bash
curl http://localhost
```

This should return the default page served by Apache or Nginx, indicating the web server is working.

Alternatively, access the server’s IP or domain name through a web browser. You should see the default web server page.

---

### Example for Creating a User, Group, and Setting Permissions for Apache:
1. Create a user:
   ```bash
   adduser webuser
   ```

2. Create a group:
   ```bash
   addgroup webgroup
   ```

3. Add the user to the group:
   ```bash
   usermod -aG webgroup webuser
   ```

4. Set the appropriate permissions on the web server's document root:
   ```bash
   chown -R webuser:webgroup /var/www/ezzeteam
   chmod -R 755 /var/www/ezzeteam
   ```

5. Install Apache:
   ```bash
   apt update
   apt install apache2
   ```

6. Enable and start Apache:
   ```bash
   systemctl enable apache2
   systemctl start apache2
   ```

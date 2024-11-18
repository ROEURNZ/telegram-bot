# Deployment Processes on Windows and Ubuntu Webserver

### 1. **Setting Up the Project on Windows with Bash**

- **Basic Commands**:
  1. Install **Git Bash** and set it up in your system’s environment variables for global Git access.
  2. To open **Git Bash** on Windows, press `Windows + R`, paste the following in the dialog box: `"C:\Program Files\Git\git-bash.exe"`, and hit Enter.
  3. **Windows 11** users may already have zip extensions installed, so no need to install them separately.

### 2. **Zipping, Unzipping, and Installing Files**

- **To Zip**:  
  Use the following command to zip the project folder:
  ```bash
  zip -r /c/Users/ROEURN/Development/Projects/PHP/Bots/telegram-bot.zip /c/Users/ROEURN/Development/Projects/PHP/Bots/telegram-bot
  ```

### 3. **Transferring the Zipped File to the Server**

- Open **Git Bash** and run the following command to transfer the zipped file to your Ubuntu server:
  ```bash
  scp /c/Users/ROEURN/Development/Projects/PHP/Bots/telegram-bot.zip root@178.128.17.107:/var/www/ezzeteam
  ```
- You’ll need the following for SSH access:
  1. **Authorized Keys**: `authorized_keys`
  2. **ID RSA Private**: `dev_id_rsa`
  3. **ID RSA Public**: `dev_id_rsa.pub`
- Enter your password when prompted (it will be hidden for security).

### 4. **Server-Side Configuration (Ubuntu Web Server)**

- **Pre-requisites**:
  1. Ensure your **Ubuntu web server** is already set up.
  2. Create a **user account** for the project.
  3. Set up **permissions** for the account and project access.
  4. Set up a **user group** for the project.
  
- **Project Directory and Configuration**:
  1. **SSL Setup**: Install and configure your SSL certificates (e.g., using Let’s Encrypt).
  2. **DNS & Sub-domain Setup**: Set up your DNS domain and sub-domain to point to your server.
  3. **Grant Permissions**: Ensure the proper access and permissions are set for the project files.

### 5. **Extracting and Setting Up the Project on the Server**

- Navigate to the project directory on the server:
  ```bash
  cd /var/www/ezzeteam/
  ```

- **Install Zip Extension** (if not already installed):
  ```bash
  sudo apt install zip
  ```

- **Unzip the Transferred File**:
  ```bash
  unzip telegram-bot.zip
  ```

- **Delete the Zipped File** (if no longer needed):
  ```bash
  rm -rf telegram-bot.zip
  ```

- **Rename the Project Folder** (Optional):
  ```bash
  mv telegram-bot barcode-dev
  ```

- **Check Files in the Directory**:
  ```bash
  ls -la
  ```

- **Navigate into the Project Directory**:
  ```bash
  cd barcode-dev
  ls -la
  ```

- **Edit Files (if necessary)**:
  If you need to create or modify any files, you can use **nano** or your preferred text editor:
  ```bash
  nano filename.extension
  ```
  - Save changes by pressing `CTRL + X`, then `Y` to confirm, and press Enter to exit.


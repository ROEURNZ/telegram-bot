
# Telegram Bot Developing

### To Access the Server with Full Privileges:

1. **Open a Terminal or Bash:**

   - Ensure you have an SSH client installed (like OpenSSH).
   - Make sure you have set up your SSH keys (public and private) for authentication.
2. **Connect to the Server:**

   - Use the following command to initiate the SSH connection:
     ```bash
     ssh username@server_ip_address
     ```
   - For example, your command is:
     ```bash
     ssh root@***.***.**.***
     ```
   - If you are using an SSH key for authentication, you may not need to enter a password. If prompted for a passphrase for your SSH key, enter it.
3. **Enter Your Passphrase (if applicable):**

   - If you have set a passphrase for your SSH key, you will be prompted to enter it after initiating the connection.
   - For example, your passphrase might be something like `**********`.
4. **You Are Now Connected to the Server:**

   - After successful authentication, you will be logged into the server and can execute commands with root privileges.

---

### Notes:

- **Security Reminder:** It's generally not a good practice to share sensitive information like passwords or passphrases publicly.
- If you use `sudo` for executing commands that require elevated privileges, you won’t need to log in as `root` directly. For example, you can log in with a regular user and use `sudo`:

  ```bash
  ssh your_username@server_ip_address
  ```

  Then use commands like:

  ```bash
  sudo your_command
  ```

This approach enhances security by not logging in directly as the root user.



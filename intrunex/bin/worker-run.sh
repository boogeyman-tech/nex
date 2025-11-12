#!/bin/bash
while true; do
  php bin/console messenger:consume async --memory-limit=128M --time-limit=3600 -vvv
  echo "Worker crashed with exit code $?. Respawning in 5 seconds..."
  sleep 5
done
export PATH="$HOME/.symfony5/bin:$PATH"


echo 'export PATH="$HOME/.symfony5/bin:$PATH"' >> ~/.bashrc
source ~/.bashrc
sudo mv /home/codespace/.symfony5/bin/symfony /usr/local/bin/symfony
RUN curl -sS https://get.symfony.com/cli/installer | bash && \
     mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

echo '' >> ~/.bashrcexport PATH=/usr/bin:$PATH
source ~/.bashrc
php -v


sudo apt-get update
sudo apt-get install -y nmap
nmap -v


sudo apt install -y php php-cli php-common php-curl php-xml php-mbstring php-zip php-sqlite3 php-mysql php-intl php-gd unzip git curl


Here’s a complete and clear guide on how to install and configure PHP inside GitHub Codespaces, including setup for Symfony or any PHP project 👇


---

🧩 1. Check if PHP is already installed

GitHub Codespaces often comes with PHP preinstalled (especially if using a LAMP or Ubuntu base image).

Run this in your terminal inside Codespaces:

php -v

If you see something like:

PHP 8.2.12 (cli) (built: Oct 17 2024 08:00:00)

✅ Then PHP is already installed — skip to Step 4.

If it says command not found, follow the steps below.


---

⚙️ 2. Update package list

Run this command to update your Codespace environment:

sudo apt update -y


---

💾 3. Install PHP and essential extensions

Run this command to install PHP along with the most commonly used extensions (good for Symfony, Laravel, etc.):

sudo apt install -y php php-cli php-common php-curl php-xml php-mbstring php-zip php-sqlite3 php-mysql php-intl php-gd unzip git curl

👉 This installs:

php-cli → For running PHP commands

php-xml, php-mbstring, php-curl, etc. → Needed for frameworks (like Symfony)

php-sqlite3 → If you use SQLite database

php-mysql → If you use MySQL database



---

🧰 4. Verify installation

Run:

php -v

You should see PHP version info again.

Also, check installed extensions:

php -m


---

⚙️ 5. Install Composer (PHP Dependency Manager)

Composer is required to install and manage Symfony or other PHP packages.

Run these commands:

php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
sudo mv composer.phar /usr/local/bin/composer
php -r "unlink('composer-setup.php');"

Then verify:

composer -V

✅ Expected output:

Composer version 2.x.x


---

🧩 6. (Optional) Install Symfony CLI

If your Codespace is for a Symfony project:

curl -sS https://get.symfony.com/cli/installer | bash
sudo mv ~/.symfony*/bin/symfony /usr/local/bin/symfony

Check:

symfony -v


---

🧠 7. Start a local PHP server (for testing)

You can run your PHP project or Symfony app like this:

For basic PHP project:

php -S 0.0.0.0:8000 -t public

For Symfony project:

symfony serve -d

Then open the forwarded port in Codespaces (usually port 8000).


---

⚡ 8. Troubleshooting Tips

If you get permission errors, prefix commands with sudo.

If your Codespace has no internet access temporarily, run sudo apt-get update again.

For missing extensions, install individually like:

sudo apt install php-xml php-mbstring



---

✅ Final Check

Run all:

php -v
composer -V
symfony -v

You’re now fully set up for PHP development in GitHub Codespaces 🎯


---

Would you like me to give you a one-line setup script that installs everything (PHP + Composer + Symfony) automatically? You can paste and run it once in Codespaces terminal.




echo $PATH && which php && whereis php


symfony server:ca:install


sudo update-alternatives --set php /usr/bin/php8.3
sudo ln -sfn /usr/local/php/8.3.14/bin/php /home/codespace/.php/current/bin/php
echo 'export PATH="/usr/local/php/8.3.14/bin:$PATH"' >> /home/codespace/.bashrc
export PATH="/usr/local/php/8.3.14/bin:$PATH"
echo 'export PATH="/usr/local/php/8.3.14/bin:$PATH"' >> ~/.bashrc && source ~/.bashrc
sudo rm -rf /opt/php/8.0.30
hash -r

apt-get install nmap -y

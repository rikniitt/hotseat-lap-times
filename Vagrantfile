# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure("2") do |config|
  # For a complete reference, please see the online documentation at
  # https://docs.vagrantup.com.

  config.vm.box = "ubuntu/xenial64"
  config.vm.hostname ="hotseat-box"

  # Create a forwarded port mapping which allows access to a specific port
  # within the machine from a port on the host machine. In the example below,
  # accessing "localhost:8080" will access port 80 on the guest machine.
  # NOTE: This will enable public access to the opened port
  config.vm.network "forwarded_port", guest: 80, host: 8080

  # Share an additional folder to the guest VM. The first argument is
  # the path on the host to the actual folder. The second argument is
  # the path on the guest to mount the folder. And the optional third
  # argument is a set of non-required options.
  config.vm.synced_folder ".", "/vagrant", owner: "www-data", group: "ubuntu"

  config.vm.provider "virtualbox" do |vb|
    # Customize the amount of memory on the VM:
    vb.memory = "2048"
    vb.gui = true
  end

  box_config = {
    :mysql_root_password => "secret"
  }

  config.vm.provision "shell", inline: <<-SHELL
    add-apt-repository -y ppa:ondrej/php
    apt-get update
    locale-gen "en_US.UTF-8" "fi_FI.UTF-8"
    debconf-set-selections <<< "mysql-server mysql-server/root_password password #{box_config[:mysql_root_password]}"
    debconf-set-selections <<< "mysql-server mysql-server/root_password_again password #{box_config[:mysql_root_password]}"
    apt-get install -y curl git zip unzip apache2 libapache2-mod-php5.6 php5.6-curl php5.6-mcrypt mysql-server php5.6-mysql php5.6-zip php5.6-mbstring php5.6-xml
    echo ServerName $HOSTNAME >> /etc/apache2/apache2.conf
    cat > /etc/apache2/sites-available/001-vagrant.conf <<EOL
<VirtualHost *:80>
  ServerAdmin webmaster@localhost
  DocumentRoot /vagrant/public
  <Directory />
    Options FollowSymLinks
    AllowOverride None
  </Directory>
  <Directory /vagrant/public/>
    Options Indexes FollowSymLinks MultiViews
    AllowOverride All
    Require all granted
  </Directory>
</VirtualHost>
EOL
    a2dissite 000-default.conf; a2ensite 001-vagrant.conf; a2enmod rewrite; systemctl restart apache2.service
    curl -sL https://deb.nodesource.com/setup_8.x | bash -
    apt-get install -y nodejs
  SHELL

  config.vm.provision "shell", privileged:false, inline: <<-SHELL
    cd /vagrant
    curl -sS https://getcomposer.org/installer | php
    php composer.phar install --no-progress --no-suggest --no-interaction --no-ansi
    if [ ! -f ".env" ]; then
      cp .env.example .env
      sed -i -e 's/DB_USERNAME=.*/DB_USERNAME=root/' -e 's/DB_PASSWORD=.*/DB_PASSWORD=#{box_config[:mysql_root_password]}/' .env
    fi
    source .env
    mysql -h"$DB_HOST" -u"$DB_USERNAME" -p"$DB_PASSWORD" -e "CREATE DATABASE $DB_DATABASE"
    php artisan key:generate
    php artisan migrate
    npm install
  SHELL
end

# xchain
XrenoChain prototype

PHP 7.2 or more must be congigured with
--enable-dba=shared 
--enable-sockets 
--enable-pcntl

***********************************************


# Install LMDB
git clone https://github.com/LMDB/lmdb.git 
cd lmdb/libraries/liblmdb
sudo make 
sudo make install

sudo apt update
sudo apt install libdb-dev
sudo apt install lmdb-utils
sudo apt install liblmdb-dev

# Install DBA for php7
sudo add-apt-repository ppa:nacc/lp1569128
sudo apt-get update
sudo apt-get upgrade
sudo apt-get install php7.0-dba

# Install PHP
cd /tmp
rm -rf /tmp/php-7.3.2/
wget -O php-7.3.2.tar.bz2 http://nl1.php.net/get/php-7.3.2.tar.bz2/from/this/mirror
tar -xvf php-7.3.2.tar.bz2
cd /tmp/php-7.3.2/

./configure --with-config-file-path=/etc/php --enable-fd-setsize=65536  --enable-dba=shared --with-lmdb=/usr/local/lib --enable-sockets --enable-pcntl --with-curl --with-gmp

make
sudo make install

extension=dba.so
sudo echo "extension=dba.so" | sudo tee -a /etc/php/php.ini

# Install MHCrypto

cd /tmp
rm -rf /tmp/php-mhcrypto
git clone https://github.com/metahashorg/php-mhcrypto
cd php-mhcrypto
phpize
./configure --enable-mhcrypto
make
sudo make install

sudo echo "extension=mhcrypto.so" | sudo tee -a /etc/php/php.ini


 

***********************************************

cd ~/
git clone https://github.com/xrenoder/xchain.git
chmod 0755 ~/xchain/
mv -f ~/xchain/local.smp ~/xchain/local.inc


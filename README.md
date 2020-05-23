# xchain
XrenoChain prototype

PHP 7.2 or more must be congigured with
--enable-dba=shared 
--enable-sockets 
--enable-pcntl
--enable-maintainer-zts

***********************************************

# Install QDBM
cd /tmp
rm -rf /tmp/qdbm-1.8.78/
wget -O qdbm-1.8.78.tar.gz https://fallabs.com/qdbm/qdbm-1.8.78.tar.gz
tar -xvf qdbm-1.8.78.tar.gz
cd /tmp/qdbm-1.8.78/

./configure

--enable-debug : build for debugging. Enable debugging symbols, do not perform optimization, and perform static linking.
--enable-devel : build for development. Enable debugging symbols, perform optimization, and perform dynamic linking.
--enable-stable : build for stable release. Perform conservative optimization, and perform dynamic linking.
--enable-pthread : feature POSIX thread and treat global variables as thread specific data.
--disable-lock : build for environments without file locking.
--disable-mmap : build for environments without memory mapping.
--enable-zlib : feature ZLIB compression for B+ tree and inverted index.
--enable-lzo : feature LZO compression for B+ tree and inverted index.
--enable-bzip : feature BZIP2 compression for B+ tree and inverted index.
--enable-iconv : feature ICONV utilities for conversion of character encodings.

make
sudo make install

# Install DBA for php7
#sudo add-apt-repository ppa:nacc/lp1569128
sudo apt-get update
sudo apt-get upgrade
sudo apt-get install php7.0-dba

# Install PHP 7.3.2

cd /tmp
rm -rf /tmp/php-7.3.2/
wget -O php-7.3.2.tar.bz2 http://nl1.php.net/get/php-7.3.2.tar.bz2/from/this/mirror
tar -xvf php-7.3.2.tar.bz2
cd /tmp/php-7.3.2/

./configure --enable-maintainer-zts --with-config-file-path=/etc/php --enable-fd-setsize=65536  --enable-dba=shared --with-qdbm --enable-sockets --enable-pcntl --with-curl --with-gmp

make
sudo make install

sudo echo "extension=dba.so" | sudo tee -a /etc/php/php.ini

# Install Parallel for php7

cd /tmp
rm -rf /tmp/parallel/
git clone https://github.com/krakjoe/parallel.git
cd parallel
phpize
./configure --enable-parallel  
[ --enable-parallel-coverage ] [ --enable-parallel-dev ]
make
sudo make install

sudo echo "extension=parallel.so" | sudo tee -a /etc/php/php.ini

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


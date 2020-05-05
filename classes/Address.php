<?php
// TODO переписать расширение для PHP:
// - работа с бинарными данными, без промежуточного HEX
// - шифрование
// - изменить имя расширения

// TODO реализовать использование зашифрованого ключа

/**
 * Work with utils keys: sign & verification, create address & public key from private key
 */
class Address extends aBase
{
    protected static $dbgLvl = Logger::DBG_ADDR;

    private const PRIVATE_HEX_LEN = 558;
    public const PUBLIC_BIN_LEN = 248;
    public const ADDRESS_HUM_LEN = 52;

    private $privateKey = null;     // bin  279 bytes
    private $publicKey = null;      // bin  248 bytes
    private $address = null;        // bin  25 bytes

    private $privateKeyHex = null;     // hex 558 bytes
    private $publicKeyHex = null;      // hex 496 bytes
    private $addressHex = null;        // hex 50 bytes

    private $privateKeyBase16 = null;  // base16
    private $publicKeyBase16 = null;   // base16
    private $addressBase16 = null;     // base16

    public function getPublicKeyBin() : string {return $this->publicKey;}
    public function getAddressBin() : string {return $this->address;}
    public function getAddressHuman() : string {return $this->addressBase16;}

    public static function createEmpty(App $app) : self
    {
        return static::create($app);
    }

    public static function createNew(App $app, string $walletPath = null) : self
    {
        $me = static::create($app);
        $me->generate();

        if ($walletPath) {
            return $me->save($walletPath);
        }

        return $me;
    }

    public static function createFromWallet(App $app, string $addressHuman, string $walletPath) : self
    {
        $me = static::create($app);

        return $me->loadPrivateKey($addressHuman, $walletPath);
    }

    public static function createFromPublic(App $app, string $publicKeyBin) : self
    {
        $me = static::create($app);

        return $me->loadPublicKey($publicKeyBin);
    }

    protected static function create(App $app) : self
    {
        $me = new self($app);

        return $me;
    }

	public function generate() : void
	{
        mhcrypto_generate_wallet($this->privateKeyHex, $this->publicKeyHex, $this->addressHex);

        $this->privateKeyBase16 = $this->hexToBase16($this->privateKeyHex);
        $this->publicKeyBase16 = $this->hexToBase16($this->publicKeyHex);
        $this->addressBase16 = $this->hexToBase16($this->addressHex);

        $this->privateKey = hex2bin($this->privateKeyHex);
        $this->publicKey = hex2bin($this->publicKeyHex);
        $this->address = hex2bin($this->addressHex);

/*
        $this->dbg('private len = ' . strlen($this->privateKey));
        $this->dbg('public len = ' . strlen($this->publicKey));
        $this->dbg('address len = ' . strlen($this->address));
*/
	}

	private function privateToPublic() : void
	{
        mhcrypto_generate_public($this->privateKeyHex, $this->publicKeyHex);
        $this->publicKeyBase16 = $this->hexToBase16($this->publicKeyHex);
        $this->publicKey = hex2bin($this->publicKeyHex);
	}

    private function publicToAddress() : void
    {
        mhcrypto_generate_address($this->publicKeyHex, $this->addressHex);
        $this->addressBase16 = $this->hexToBase16($this->addressHex);
        $this->address = hex2bin($this->addressHex);
    }

    /**
     * Return binary signature
     * @param $data
     * @return |null
     */
    public function sign($data) : string
    {
        $sign = null;

        mhcrypto_sign_text($sign, $this->privateKeyHex, $data);

        return $sign;
    }

    /**
     * Verify binary signature
     * @param $signBin
     * @param $data
     * @return mixed
     */
    public function verify($signBin, $data) : bool
    {
        return mhcrypto_check_sign_text($signBin, $this->publicKeyHex, $data);
    }

    public static function checkAddress($addressHuman) : bool
    {
        if ($addressHuman) {
            $addressHex = static::hexFromBase16($addressHuman);
            return mhcrypto_check_address($addressHex);
        }

        return false;
    }

	public function hexToBase16($string) : string
	{
		return (strpos($string, '0x') === 0) ? $string : '0x' . $string;
	}

	public static function hexFromBase16($string) : string
	{
		return (strpos($string, '0x') === 0) ? substr($string, 2) : $string;
	}

    public function save(string $walletPath) : self
    {
        if (!is_dir($walletPath)) {
            if (!mkdir($concurrentDirectory = $walletPath) && !is_dir($concurrentDirectory)) {
                throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
        }

        $file = $walletPath . $this->addressBase16;

        if (is_file($file)) {
            return $this;
        }

        $fd = fopen($file, 'wb');
        flock($fd, LOCK_EX);
        fwrite($fd, $this->privateKeyHex);
        flock($fd, LOCK_UN);
        fclose($fd);

        return $this;
    }

    public function loadPrivateKey(string $addressHuman, string $walletPath) : self
    {
        if ($this->privateKey !== null || $this->publicKey !== null) {
            throw new RuntimeException('Cannot load new private key - this address-object is already filled');
        }

        if (!is_dir($walletPath)) {
            throw new RuntimeException(sprintf('Directory "%s" was not found', $walletPath));
        }

        if (!static::checkAddress($addressHuman)) {
            throw new RuntimeException("Address $addressHuman is not valid");
        }

        $file = $walletPath . $addressHuman;

        if (!is_file($file)) {
            throw new RuntimeException(sprintf('File "%s" was not found', $file));
        }

        if (($fileSize = filesize($file)) !== static::PRIVATE_HEX_LEN) {
            throw new RuntimeException("File '$file' size $fileSize is incorrect, need " . static::PRIVATE_HEX_LEN);
        }

        $fd = fopen($file, 'rb');
        flock($fd, LOCK_EX);
        $this->privateKeyHex = fread($fd, $fileSize);
        flock($fd, LOCK_UN);
        fclose($fd);

        $this->privateKeyBase16 = $this->hexToBase16($this->privateKeyHex);
        $this->privateKey = hex2bin($this->privateKeyHex);

        $this->privateToPublic();
        $this->publicToAddress();

        if ($this->addressBase16 !== $addressHuman) {
            throw new RuntimeException("File '$file' contains private key for address $this->addressBase16, not $addressHuman");
        }

        $this->dbg('Private key for ' . $this->addressBase16 . " loaded");

        return $this;
    }

    public function loadPublicKey($publicKeyBin) : self
    {
        if ($this->privateKey !== null || $this->publicKey !== null) {
            throw new RuntimeException('Cannot load new public key - this address-object is already filled');
        }

        if (($keySize = strlen($publicKeyBin)) !== static::PUBLIC_BIN_LEN) {
            throw new RuntimeException("Binary public key size $keySize is incorrect, need " . static::PUBLIC_BIN_LEN);
        }

        $this->publicKey = $publicKeyBin;
        $this->publicKeyHex = bin2hex($this->publicKey);
        $this->publicKeyBase16 = $this->hexToBase16($this->publicKeyHex);

        $this->publicToAddress();

        $this->dbg('Private key for ' . $this->addressBase16 . " loaded");

        return $this;
    }
}
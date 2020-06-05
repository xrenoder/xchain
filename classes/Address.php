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
    protected static $dbgLvl = Logger::DBG_ADDRESS;

    private const PRIVATE_HEX_LEN = 558;
    public const PUBLIC_BIN_LEN = 248;
    public const ADDRESS_HUM_LEN = 52;
    public const ADDRESS_BIN_LEN = 25;

    public const HASH_BIN_LEN = 16;

    private $publicKey = null;      // bin  248 bytes
    private $address = null;        // bin  25 bytes

    private $privateKeyHex = null;     // hex 558 bytes
    private $publicKeyHex = null;      // hex 496 bytes
    private $addressHex = null;        // hex 50 bytes

    private $addressBase16 = null;     // base16 (hex + 2 bytes)

    public function getPublicKeyBin() : string {return $this->publicKey;}
    public function getAddressBin() : string {return $this->address;}
    public function getAddressHuman() : string {return $this->addressBase16;}

    /** @var bool */
    private $addrOnly = false;
    public function isAddressOnly() : bool {return $this->addrOnly;}

    public static function createNew(aLocator $locator, string $walletPath = null) : self
    {
        $me = static::create($locator);
        $me->generate();

        if ($walletPath) {
            return $me->save($walletPath);
        }

        return $me;
    }

    public static function createFromWallet(aLocator $locator, string $addressHuman, string $walletPath) : self
    {
        $me = static::create($locator);

        return $me->loadPrivateKey($addressHuman, $walletPath);
    }

    public static function createFromPrivateHex(aLocator $locator, string $privateHex) : self
    {
        $me = static::create($locator);

        return $me->createFromPirvate($privateHex);
    }

    public static function createFromPublic(aLocator $locator, string $publicKeyBin) : self
    {
        $me = static::create($locator);

        return $me->loadPublicKey($publicKeyBin);
    }

    public static function createFromAddress(aLocator $locator, string $addressBin) : self
    {
        $me = static::create($locator);

        return $me->setAddress($addressBin);
    }

    protected static function create(aLocator $locator) : self
    {
        return new self($locator);
    }

    public function setAddress(string $addressBin) : self
    {
        if ($this->privateKeyHex !== null || $this->publicKeyHex !== null || $this->address !== null) {
            throw new RuntimeException('Cannot load new address - this address-object is already filled');
        }

        $this->address = $addressBin;
        $this->addressHex = bin2hex($addressBin);
        $this->addressBase16 = static::hexToBase16($this->addressHex);
        $this->addrOnly = true;

        return $this;
    }

	public function generate() : void
	{
        mhcrypto_generate_wallet($this->privateKeyHex, $this->publicKeyHex, $this->addressHex);

        $this->addressBase16 = static::hexToBase16($this->addressHex);

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
        $this->publicKey = hex2bin($this->publicKeyHex);
	}

    private function publicToAddress() : void
    {
        mhcrypto_generate_address($this->publicKeyHex, $this->addressHex);
        $this->addressBase16 = static::hexToBase16($this->addressHex);
        $this->address = hex2bin($this->addressHex);
    }

    /**
     * Return binary signature
     * @param $data
     * @return |null
     */
    public function signBin($data) : string
    {
        if ($this->privateKeyHex === null ) {
            throw new Exception("Cannot sign data without private key");
        }

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
    public function verifyBin($signBin, $data) : bool
    {
        if ($this->publicKeyHex === null ) {
            throw new Exception("Cannot verify signature without public key");
        }

        return mhcrypto_check_sign_text($signBin, $this->publicKeyHex, $data);
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
        if (!is_dir($walletPath)) {
            throw new RuntimeException(sprintf('Directory "%s" was not found', $walletPath));
        }

        if (!static::checkAddressHuman($addressHuman)) {
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
        $privateKeyHex = fread($fd, $fileSize);
        flock($fd, LOCK_UN);
        fclose($fd);

        return $this->createFromPirvate($privateKeyHex, $addressHuman, $file);
    }

    public function createFromPirvate($privateHex, $addressHuman = null, $file = null) : self
    {
        if ($this->privateKeyHex !== null || $this->publicKeyHex !== null || $this->address !== null) {
            throw new RuntimeException('Cannot load new private key - this address-object is already filled');
        }

        $this->privateKeyHex = $privateHex;

        $this->privateToPublic();
        $this->publicToAddress();

        if ($file !== null && $addressHuman !== null && $this->addressBase16 !== $addressHuman) {
            throw new RuntimeException("File '$file' contains private key for address $this->addressBase16, not $addressHuman");
        }

        $this->dbg('Private key for ' . $this->addressBase16 . " loaded");

        return $this;
    }

    public function loadPublicKey($publicKeyBin, $addressBin = null) : self
    {
        if ($this->privateKeyHex !== null || $this->publicKeyHex !== null || $this->address !== null) {
            throw new RuntimeException('Cannot load new public key - this address-object is already filled');
        }

        if (($keySize = strlen($publicKeyBin)) !== static::PUBLIC_BIN_LEN) {
            throw new RuntimeException("Binary public key size $keySize is incorrect, need " . static::PUBLIC_BIN_LEN);
        }

        $this->publicKey = $publicKeyBin;
        $this->publicKeyHex = bin2hex($this->publicKey);

        $this->publicToAddress();

        if ($addressBin !== null && $addressBin !== $this->address) {
            throw new RuntimeException('Cannot load: this public key is not for address ' . static::hexToBase16(bin2hex($addressBin)));
        }

        $this->dbg('Public key for ' . $this->addressBase16 . " loaded");

        return $this;
    }

    public static function checkAddressBin($addressBin) : bool
    {
        if ($addressBin) {
            $addressHex = bin2hex($addressBin);
            return mhcrypto_check_address($addressHex);
        }

        return false;
    }

    public static function checkAddressHuman($addressHuman) : bool
    {
        if ($addressHuman) {
            $addressHex = static::hexFromBase16($addressHuman);
            return mhcrypto_check_address($addressHex);
        }

        return false;
    }

    public static function binToBase16($string) : string
    {
        $hex = bin2hex($string);
        return '0x' . $hex;
    }

	public static function hexToBase16($string) : string
	{
		return (strpos($string, '0x') === 0) ? $string : '0x' . $string;
	}

	public static function hexFromBase16($string) : string
	{
		return (strpos($string, '0x') === 0) ? substr($string, 2) : $string;
	}
}
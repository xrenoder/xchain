<?php
// TODO переписать расширение для PHP:
// - работа с бинарными данными, без промежуточного HEX
// - шифрование
// - изменить имя расширения

/**
 * Work with crypto keys: sign & verification, create address & public key from private key
 */
class Address extends aBase
{
    protected static $dbgLvl = Logger::DBG_ADDR;

    private $privateKey = null;     // bin  279 bytes
    private $publicKey = null;      // bin  248 bytes
    private $address = null;        // bin  25 bytes

    private $privateKeyHex = null;     // hex 558 bytes
    private $publicKeyHex = null;      // hex 496 bytes
    private $addressHex = null;        // hex 50 bytes

    private $privateKeyBase16 = null;  // base16
    private $publicKeyBase16 = null;   // base16
    private $addressBase16 = null;     // base16

    public static function createEmpty(App $app) : self
    {
        return static::create($app);
    }

    public static function createNew(App $app, string $walletPath = null) : self
    {
        $me = static::createEmpty($app);
        $me->generate();

        if ($walletPath) {
            return $me->save($walletPath);
        }

        return $me;
    }

    public static function createFromFile(App $app, string $privateKeyFile) : self
    {
        $me = static::createEmpty($app);
    }

    protected static function create(App $app, string $privateKey = null, $publicKey = null, $address = null) : self
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
        $this->dbg(static::$dbgLvl, 'private len = ' . strlen($this->privateKey));
        $this->dbg(static::$dbgLvl, 'public len = ' . strlen($this->publicKey));
        $this->dbg(static::$dbgLvl, 'address len = ' . strlen($this->address));
*/
	}
/*
	public function privateToPublic($privateKeyBase16)
	{
		$result = null;

        $public_key = null;
        mhcrypto_generate_public($this->parse_base16($private_key), $public_key);


        $result = $this->to_base16($public_key);

		return $public_key;
	}
	
	public function sign($data, $private_key, $rand = false, $algo = 'sha256')
	{
		$sign = null;
		
//		mhcrypto_sign_text($sign, $this->parse_base16($private_key), $data);
        mhcrypto_sign_text($sign, $private_key, $data);

//		return '0x'.bin2hex($sign);
        return $sign;
	}

	public function verify($sign, $data, $public_key, $algo = 'sha256')
	{
//      $result = mhcrypto_check_sign_text($this->hex2bin($sign), $this->parse_base16($public_key), $data);
        $result = mhcrypto_check_sign_text($sign, $public_key, $data);
		return $result;
	}

	public function getAddress($publicKey)
	{
        $address = null;
        mhcrypto_generate_address($publicKey, $address);

//		return $this->to_base16($address);
        return $address;
	}

	public function checkAdress($address)
	{
		if(!empty($address))
		{
//            return mhcrypto_check_address($this->parse_base16($address));
            return mhcrypto_check_address($address);
		}
		return false;
	}
*/
	public function hexToBase16($string)
	{
		return (substr($string, 0, 2) === '0x') ? $string : '0x' . $string;
	}

	public function hexFromBase16($string)
	{
		return (substr($string, 0, 2) === '0x') ? substr($string, 2) : $string;
	}

    public function save(string $walletPath) : ?self
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

        $fd = fopen($file, 'w+b');
        flock($fd, LOCK_EX);
        fwrite($fd, $this->privateKeyHex);
        flock($fd, LOCK_UN);
        fclose($fd);

        return $this;
    }
}
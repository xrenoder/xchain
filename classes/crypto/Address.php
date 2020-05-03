<?php

class Address extends aBase
{
    protected static $dbgLvl = Logger::DBG_ADDR;

    private $privateKey = null;     // bin
    private $publicKey = null;      // bin
    private $address = null;        // bin

    private $privateKeyHex = null;     // hex
    private $publicKeyHex = null;      // hex
    private $addressHex = null;        // hex

    private $privateKeyBase16 = null;  // base16
    private $publicKeyBase16 = null;   // base16
    private $addressBase16 = null;     // base16

    public static function createEmpty(App $app) : self
    {
        return static::create($app);
    }

    public static function createNew(App $app) : self
    {
        $me = static::createEmpty($app);
        $me->generate();

        return $me;
    }

    public static function createFromPrivate(App $app, string $privateKey) : self
    {
        $me = static::createEmpty($app);
        $me->generate();
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
}
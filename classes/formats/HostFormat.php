<?php


class HostFormat extends aFixLengthFieldFormat
{
    protected function packDataTransform($data) : string
    {
        $tmp = explode(':', trim($data));

        if (count($tmp) !== 2) {
            throw new Exception("Bad value of " . $this->getName() . ": $data must have format 'IP:port'");
        }

        $port = trim($tmp[1]);

        if (!is_int($port) || $port < 1 || $port > 65535) {
            throw new Exception("Bad value of " . $this->getName() . ": $data - wrong port'");
        }

        $tmp = explode('.', trim($tmp[0]));

        if (count($tmp) !== 4) {
            throw new Exception("Bad value of " . $this->getName() . ": $data - wrong IP'");
        }

        $result = '';

        for ($i = 0; $i < 4; $i++) {
            $ipPart = trim($tmp[$i]);

            if (!is_int($ipPart) || $ipPart < 0 || $ipPart > 255) {
                throw new Exception("Bad value of " . $this->getName() . ": $data - wrong IP'");
            }

            $result .= pack('C', $ipPart);
        }

        $result .= pack('n', $port);

        return $result;
    }

    protected function unpackRawTransform()
    {
        $this->value = '';

        for($i = 0; $i < 4; $i++) {
            $this->value .= unpack('C', $this->rawWithoutLength[$i])[1];

            if ($i < 3) {
                $this->value .= '.';
            }
        }

        $this->value .= ':';
        $this->value .= unpack('n', substr($this->rawWithoutLength, 4, 2))[1];

        return $this->value;
    }
}
<?php

namespace WebArch\BitrixEmailAsLogin\Test\Fixture;

class BitrixFixture
{
    /**
     * Копия глобальной функции bool check_email(string email, bool Strict = false) для независимости Unit-тестов от
     * ядра Битрикс.
     *
     * @param mixed $email
     * @param bool $bStrict
     *
     * @return bool
     *
     * @link https://dev.1c-bitrix.ru/api_help/main/functions/other/check_email.php
     */
    public static function checkEmail($email, $bStrict = false): bool
    {
        if (!$bStrict) {
            $email = trim($email);
            if (preg_match("#.*?[<\\[\\(](.*?)[>\\]\\)].*#i", $email, $arr) && strlen($arr[1]) > 0) {
                $email = $arr[1];
            }
        }

        //http://tools.ietf.org/html/rfc2821#section-4.5.3.1
        //4.5.3.1. Size limits and minimums
        if (strlen($email) > 320) {
            return false;
        }

        //http://tools.ietf.org/html/rfc2822#section-3.2.4
        //3.2.4. Atom
        static $atom = "=_0-9a-z+~'!\$&*^`|\\#%/?{}-";

        //"." can't be in the beginning or in the end of local-part
        //dot-atom-text = 1*atext *("." 1*atext)
        if (preg_match("#^[" . $atom . "]+(\\.[" . $atom . "]+)*@(([-0-9a-z_]+\\.)+)([a-z0-9-]{2,20})$#i", $email)) {
            return true;
        } else {
            return false;
        }
    }
}

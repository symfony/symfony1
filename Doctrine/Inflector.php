<?php
/*
 *  $Id: Inflector.php 3189 2007-11-18 20:37:44Z meus $
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information, see
 * <http://www.phpdoctrine.org>.
 */

/**
 * Doctrine_Inflector has static methods for inflecting text
 * 
 * The methods in these classes are from several different sources collected
 * across several different php projects and several different authors. The 
 * original author names and emails are not known
 *
 * @package     Doctrine
 * @subpackage  Inflector
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.phpdoctrine.org
 * @since       1.0
 * @version     $Revision: 3189 $
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 */
class Doctrine_Inflector
{
    /**
    * pluralize
    *
    * @param    string $word English noun to pluralize
    * @return   string Plural noun
    */
    public static function pluralize($word)
    {
        $plural = array('/(quiz)$/i' => '\1zes',
                        '/^(ox)$/i' => '\1en',
                        '/([m|l])ouse$/i' => '\1ice',
                        '/(matr|vert|ind)ix|ex$/i' => '\1ices',
                        '/(x|ch|ss|sh)$/i' => '\1es',
                        '/([^aeiouy]|qu)ies$/i' => '\1y',
                        '/([^aeiouy]|qu)y$/i' => '\1ies',
                        '/(hive)$/i' => '\1s',
                        '/(?:([^f])fe|([lr])f)$/i' => '\1\2ves',
                        '/sis$/i' => 'ses',
                        '/([ti])um$/i' => '\1a',
                        '/(buffal|tomat)o$/i' => '\1oes',
                        '/(bu)s$/i' => '\1ses',
                        '/(alias|status)/i' => '\1es',
                        '/(octop|vir)us$/i' => '\1i',
                        '/(ax|test)is$/i' => '\1es',
                        '/s$/i' => 's',
                        '/$/' => 's');

        $uncountable = array('equipment',
                             'information',
                             'rice',
                             'money',
                             'species',
                             'series',
                             'fish',
                             'sheep');

        $irregular = array('person' => 'people',
                           'man'    => 'men',
                           'child'  => 'children',
                           'sex'    => 'sexes',
                           'move'   => 'moves');

        $lowercasedWord = strtolower($word);

        foreach ($uncountable as $_uncountable) {
            if(substr($lowercasedWord, (-1 * strlen($_uncountable))) == $_uncountable) {
                return $word;
            }
        }

        foreach ($irregular as $_plural=> $_singular){
            if (preg_match('/('.$_plural.')$/i', $word, $arr)) {
                return preg_replace('/('.$_plural.')$/i', substr($arr[0],0,1) . substr($_singular,1), $word);
            }
        }

        foreach ($plural as $rule => $replacement) {
            if (preg_match($rule, $word)) {
                return preg_replace($rule, $replacement, $word);
            }
        }

        return false;
    }

    /**
    * singularize
    *
    * @param    string    $word    English noun to singularize
    * @return   string Singular noun.
    */
    public static function singularize($word)
    {
        $singular = array('/(quiz)zes$/i' => '\\1',
                          '/(matr)ices$/i' => '\\1ix',
                          '/(vert|ind)ices$/i' => '\\1ex',
                          '/^(ox)en/i' => '\\1',
                          '/(alias|status)es$/i' => '\\1',
                          '/([octop|vir])i$/i' => '\\1us',
                          '/(cris|ax|test)es$/i' => '\\1is',
                          '/(shoe)s$/i' => '\\1',
                          '/(o)es$/i' => '\\1',
                          '/(bus)es$/i' => '\\1',
                          '/([m|l])ice$/i' => '\\1ouse',
                          '/(x|ch|ss|sh)es$/i' => '\\1',
                          '/(m)ovies$/i' => '\\1ovie',
                          '/(s)eries$/i' => '\\1eries',
                          '/([^aeiouy]|qu)ies$/i' => '\\1y',
                          '/([lr])ves$/i' => '\\1f',
                          '/(tive)s$/i' => '\\1',
                          '/(hive)s$/i' => '\\1',
                          '/([^f])ves$/i' => '\\1fe',
                          '/(^analy)ses$/i' => '\\1sis',
                          '/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => '\\1\\2sis',
                          '/([ti])a$/i' => '\\1um',
                          '/(n)ews$/i' => '\\1ews',
                          '/^(.{2,2})$/i' => '\\1',
                          '/s$/i' => '');

        $uncountable = array('equipment',
                             'information',
                             'rice',
                             'money',
                             'species',
                             'series',
                             'fish',
                             'sheep',
                             'sms',
                             'status',
                             'access');

        $irregular = array('person' => 'people',
                           'man'    => 'men',
                           'child'  => 'children',
                           'sex'    => 'sexes',
                           'move'   => 'moves');

        $lowercasedWord = strtolower($word);
        foreach ($uncountable as $_uncountable){
            if(substr($lowercasedWord, ( -1 * strlen($_uncountable))) == $_uncountable){
                return $word;
            }
        }

        foreach ($irregular as $_singular => $_plural) {
            if (preg_match('/('.$_plural.')$/i', $word, $arr)) {
                return preg_replace('/('.$_plural.')$/i', substr($arr[0],0,1).substr($_singular,1), $word);
            }
        }

        foreach ($singular as $rule => $replacement) {
            if (preg_match($rule, $word)) {
                return preg_replace($rule, $replacement, $word);
            }
        }

        return $word;
    }

    /**
     * variablize
     * 
     * @param string $word 
     * @return void
     */
    public static function variablize($word)
    {
        $word = self::camelize($word);

        return strtolower($word[0]) . substr($word, 1);
    }

    /**
     * tableize
     *
     * @param string $name
     * @return void
     */
    public static function tableize($name)
    {
        // Would prefer this but it breaks unit tests. Forces the table underscore pattern
        // return self::pluralize(self::underscore($name));
        return strtolower(preg_replace('~(?<=\\w)([A-Z])~', '_$1', $name));
    }

    /**
     * classify
     *
     * @param string $word
     */
    public static function classify($word)
    {
        return preg_replace_callback('~(_?)(_)([\w])~', array("Doctrine_Inflector", "classifyCallback"), ucfirst(strtolower($word)));
    }

    /**
     * classifyCallback
     *
     * Callback function to classify a classname properly.
     *
     * @param array $matches An array of matches from a pcre_replace call
     * @return string A string with matches 1 and mathces 3 in upper case.
     */
    public static function classifyCallback($matches)
    {
        return $matches[1] . strtoupper($matches[3]);
    }

    /**
     * camelize
     *
     * @param string $word 
     * @return void
     */
    public static function camelize($word)
    {
        if (preg_match_all('/\/(.?)/', $word, $got)) {
            foreach ($got[1] as $k => $v){
                $got[1][$k] = '::' . strtoupper($v);
            }

            $word = str_replace($got[0], $got[1], $word);
        }

        return str_replace(' ', '', ucwords(preg_replace('/[^A-Z^a-z^0-9^:]+/', ' ', $word)));
    }

    /**
     * seemsUtf8
     *
     * By bmorel at ssi dot fr
     *
     * @param string $str 
     * @return void
     */
    public static function seemsUtf8($string)
    {
    	for ($i = 0; $i < strlen($string); $i++) {
    		if (ord($string[$i]) < 0x80) continue; # 0bbbbbbb
    		elseif ((ord($string[$i]) & 0xE0) == 0xC0) $n=1; # 110bbbbb
    		elseif ((ord($string[$i]) & 0xF0) == 0xE0) $n=2; # 1110bbbb
    		elseif ((ord($string[$i]) & 0xF8) == 0xF0) $n=3; # 11110bbb
    		elseif ((ord($string[$i]) & 0xFC) == 0xF8) $n=4; # 111110bb
    		elseif ((ord($string[$i]) & 0xFE) == 0xFC) $n=5; # 1111110b
    		else return false; # Does not match any model
    		for ($j=0; $j<$n; $j++) { # n bytes matching 10bbbbbb follow ?
    			if ((++$i == strlen($string)) || ((ord($string[$i]) & 0xC0) != 0x80))
    			return false;
    		}
    	}
    	return true;
    }
    
    /**
     * unaccent
     *
     * @param string $string 
     * @return void
     */
    public static function unaccent($string)
    {
      	if ( ! preg_match('/[\x80-\xff]/', $string) ) {
      		return $string;
  		}

      	if (self::seemsUtf8($string)) {
      		$chars = array(
      		// Decompositions for Latin-1 Supplement
      		chr(195).chr(128) => 'A', chr(195).chr(129) => 'A',
      		chr(195).chr(130) => 'A', chr(195).chr(131) => 'A',
      		chr(195).chr(132) => 'A', chr(195).chr(133) => 'A',
      		chr(195).chr(135) => 'C', chr(195).chr(136) => 'E',
      		chr(195).chr(137) => 'E', chr(195).chr(138) => 'E',
      		chr(195).chr(139) => 'E', chr(195).chr(140) => 'I',
      		chr(195).chr(141) => 'I', chr(195).chr(142) => 'I',
      		chr(195).chr(143) => 'I', chr(195).chr(145) => 'N',
      		chr(195).chr(146) => 'O', chr(195).chr(147) => 'O',
      		chr(195).chr(148) => 'O', chr(195).chr(149) => 'O',
      		chr(195).chr(150) => 'O', chr(195).chr(153) => 'U',
      		chr(195).chr(154) => 'U', chr(195).chr(155) => 'U',
      		chr(195).chr(156) => 'U', chr(195).chr(157) => 'Y',
      		chr(195).chr(159) => 's', chr(195).chr(160) => 'a',
      		chr(195).chr(161) => 'a', chr(195).chr(162) => 'a',
      		chr(195).chr(163) => 'a', chr(195).chr(164) => 'a',
      		chr(195).chr(165) => 'a', chr(195).chr(167) => 'c',
      		chr(195).chr(168) => 'e', chr(195).chr(169) => 'e',
      		chr(195).chr(170) => 'e', chr(195).chr(171) => 'e',
      		chr(195).chr(172) => 'i', chr(195).chr(173) => 'i',
      		chr(195).chr(174) => 'i', chr(195).chr(175) => 'i',
      		chr(195).chr(177) => 'n', chr(195).chr(178) => 'o',
      		chr(195).chr(179) => 'o', chr(195).chr(180) => 'o',
      		chr(195).chr(181) => 'o', chr(195).chr(182) => 'o',
      		chr(195).chr(182) => 'o', chr(195).chr(185) => 'u',
      		chr(195).chr(186) => 'u', chr(195).chr(187) => 'u',
      		chr(195).chr(188) => 'u', chr(195).chr(189) => 'y',
      		chr(195).chr(191) => 'y',
      		// Decompositions for Latin Extended-A
      		chr(196).chr(128) => 'A', chr(196).chr(129) => 'a',
      		chr(196).chr(130) => 'A', chr(196).chr(131) => 'a',
      		chr(196).chr(132) => 'A', chr(196).chr(133) => 'a',
      		chr(196).chr(134) => 'C', chr(196).chr(135) => 'c',
      		chr(196).chr(136) => 'C', chr(196).chr(137) => 'c',
      		chr(196).chr(138) => 'C', chr(196).chr(139) => 'c',
      		chr(196).chr(140) => 'C', chr(196).chr(141) => 'c',
      		chr(196).chr(142) => 'D', chr(196).chr(143) => 'd',
      		chr(196).chr(144) => 'D', chr(196).chr(145) => 'd',
      		chr(196).chr(146) => 'E', chr(196).chr(147) => 'e',
      		chr(196).chr(148) => 'E', chr(196).chr(149) => 'e',
      		chr(196).chr(150) => 'E', chr(196).chr(151) => 'e',
      		chr(196).chr(152) => 'E', chr(196).chr(153) => 'e',
      		chr(196).chr(154) => 'E', chr(196).chr(155) => 'e',
      		chr(196).chr(156) => 'G', chr(196).chr(157) => 'g',
      		chr(196).chr(158) => 'G', chr(196).chr(159) => 'g',
      		chr(196).chr(160) => 'G', chr(196).chr(161) => 'g',
      		chr(196).chr(162) => 'G', chr(196).chr(163) => 'g',
      		chr(196).chr(164) => 'H', chr(196).chr(165) => 'h',
      		chr(196).chr(166) => 'H', chr(196).chr(167) => 'h',
      		chr(196).chr(168) => 'I', chr(196).chr(169) => 'i',
      		chr(196).chr(170) => 'I', chr(196).chr(171) => 'i',
      		chr(196).chr(172) => 'I', chr(196).chr(173) => 'i',
      		chr(196).chr(174) => 'I', chr(196).chr(175) => 'i',
      		chr(196).chr(176) => 'I', chr(196).chr(177) => 'i',
      		chr(196).chr(178) => 'IJ',chr(196).chr(179) => 'ij',
      		chr(196).chr(180) => 'J', chr(196).chr(181) => 'j',
      		chr(196).chr(182) => 'K', chr(196).chr(183) => 'k',
      		chr(196).chr(184) => 'k', chr(196).chr(185) => 'L',
      		chr(196).chr(186) => 'l', chr(196).chr(187) => 'L',
      		chr(196).chr(188) => 'l', chr(196).chr(189) => 'L',
      		chr(196).chr(190) => 'l', chr(196).chr(191) => 'L',
      		chr(197).chr(128) => 'l', chr(197).chr(129) => 'L',
      		chr(197).chr(130) => 'l', chr(197).chr(131) => 'N',
      		chr(197).chr(132) => 'n', chr(197).chr(133) => 'N',
      		chr(197).chr(134) => 'n', chr(197).chr(135) => 'N',
      		chr(197).chr(136) => 'n', chr(197).chr(137) => 'N',
      		chr(197).chr(138) => 'n', chr(197).chr(139) => 'N',
      		chr(197).chr(140) => 'O', chr(197).chr(141) => 'o',
      		chr(197).chr(142) => 'O', chr(197).chr(143) => 'o',
      		chr(197).chr(144) => 'O', chr(197).chr(145) => 'o',
      		chr(197).chr(146) => 'OE',chr(197).chr(147) => 'oe',
      		chr(197).chr(148) => 'R', chr(197).chr(149) => 'r',
      		chr(197).chr(150) => 'R', chr(197).chr(151) => 'r',
      		chr(197).chr(152) => 'R', chr(197).chr(153) => 'r',
      		chr(197).chr(154) => 'S', chr(197).chr(155) => 's',
      		chr(197).chr(156) => 'S', chr(197).chr(157) => 's',
      		chr(197).chr(158) => 'S', chr(197).chr(159) => 's',
      		chr(197).chr(160) => 'S', chr(197).chr(161) => 's',
      		chr(197).chr(162) => 'T', chr(197).chr(163) => 't',
      		chr(197).chr(164) => 'T', chr(197).chr(165) => 't',
      		chr(197).chr(166) => 'T', chr(197).chr(167) => 't',
      		chr(197).chr(168) => 'U', chr(197).chr(169) => 'u',
      		chr(197).chr(170) => 'U', chr(197).chr(171) => 'u',
      		chr(197).chr(172) => 'U', chr(197).chr(173) => 'u',
      		chr(197).chr(174) => 'U', chr(197).chr(175) => 'u',
      		chr(197).chr(176) => 'U', chr(197).chr(177) => 'u',
      		chr(197).chr(178) => 'U', chr(197).chr(179) => 'u',
      		chr(197).chr(180) => 'W', chr(197).chr(181) => 'w',
      		chr(197).chr(182) => 'Y', chr(197).chr(183) => 'y',
      		chr(197).chr(184) => 'Y', chr(197).chr(185) => 'Z',
      		chr(197).chr(186) => 'z', chr(197).chr(187) => 'Z',
      		chr(197).chr(188) => 'z', chr(197).chr(189) => 'Z',
      		chr(197).chr(190) => 'z', chr(197).chr(191) => 's',
      		// Euro Sign
      		chr(226).chr(130).chr(172) => 'E',
      		// GBP (Pound) Sign
      		chr(194).chr(163) => '');

      		$string = strtr($string, $chars);
      	} else {
      		// Assume ISO-8859-1 if not UTF-8
      		$chars['in'] = chr(128).chr(131).chr(138).chr(142).chr(154).chr(158)
      			.chr(159).chr(162).chr(165).chr(181).chr(192).chr(193).chr(194)
      			.chr(195).chr(196).chr(197).chr(199).chr(200).chr(201).chr(202)
      			.chr(203).chr(204).chr(205).chr(206).chr(207).chr(209).chr(210)
      			.chr(211).chr(212).chr(213).chr(214).chr(216).chr(217).chr(218)
      			.chr(219).chr(220).chr(221).chr(224).chr(225).chr(226).chr(227)
      			.chr(228).chr(229).chr(231).chr(232).chr(233).chr(234).chr(235)
      			.chr(236).chr(237).chr(238).chr(239).chr(241).chr(242).chr(243)
      			.chr(244).chr(245).chr(246).chr(248).chr(249).chr(250).chr(251)
      			.chr(252).chr(253).chr(255);

      		$chars['out'] = "EfSZszYcYuAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy";

      		$string = strtr($string, $chars['in'], $chars['out']);
      		$doubleChars['in'] = array(chr(140), chr(156), chr(198), chr(208), chr(222), chr(223), chr(230), chr(240), chr(254));
      		$doubleChars['out'] = array('OE', 'oe', 'AE', 'DH', 'TH', 'ss', 'ae', 'dh', 'th');
      		$string = str_replace($doubleChars['in'], $doubleChars['out'], $string);
      	}

      	return $string;
    }

    /**
     * urlize
     *
     * @param string $text 
     * @return void
     */
    public static function urlize($text)
    {
        // Remove all non url friendly characters with the unaccent function
        $text = self::unaccent($text);
        
        // Remove all none word characters
        $text = preg_replace('/\W/', ' ', $text);
        
        // More stripping. Replace spaces with dashes
        $text = strtolower(preg_replace('/[^A-Z^a-z^0-9^\/]+/', '-',
                           preg_replace('/([a-z\d])([A-Z])/', '\1_\2',
                           preg_replace('/([A-Z]+)([A-Z][a-z])/', '\1_\2',
                           preg_replace('/::/', '/', $text)))));
        
        return trim($text, '-');
    }

    /**
     * underscore
     *
     * @param string $word 
     * @return void
     */
    public static function underscore($word)
    {
        return strtolower(preg_replace('/[^A-Z^a-z^0-9^\/]+/', '_',
               preg_replace('/([a-z\d])([A-Z])/', '\1_\2',
               preg_replace('/([A-Z]+)([A-Z][a-z])/', '\1_\2',
               preg_replace('/::/', '/', $word)))));
    }
}
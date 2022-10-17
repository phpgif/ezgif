<?php
###############################################################
# Modified by @FakeryBakery (https://github.com/FakeryBakery) #
# to support PHP 8. Originally modified by ErikvdVen          #
# (https://github.com/ErikvdVen) for ErikvdVen's project,     #
# php-gif (https://github.com/ErikvdVen/php-gif).             #
###############################################################
/*
:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
::
::	GIFEncoder Version 3.0 by L�szl� Zsidi, http://gifs.hu
::
::	This class is a rewritten 'GifMerge.class.php' version.
::
::  Modification:
::   - Simplified and easy code,
::   - Ultra fast encoding,
::   - Built-in errors,
::   - Stable working
::
::
::	Updated at 2007. 02. 13. '00.05.AM'
::
::
::
::  Try on-line GIFBuilder Form demo based on GIFEncoder.
::
::  http://gifs.hu/phpclasses/demos/GifBuilder/
::
:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
*/
class GIFEncoder {
    var $GIF = "GIF89a";		/* GIF header 6 bytes	*/
    var $VER = "GIFEncoder V3.00";	/* Encoder version		*/
    var $BUF = [];
    var $OFS = [];
    var $SIG =  0;
    var $LOP =  0;
    var $DIS =  2;
    var $COL = -1;
    var $IMG = -1;
    var $ERR = [
        'ERR00'=>"Does not supported function for only one image!",
        'ERR01'=>"Source is not a GIF image!",
        'ERR02'=>"Unintelligible flag ",
        'ERR03'=>"Does not make animation from animated GIF source",
    ]
    ;
    /*
    :::::::::::::::::::::::::::::::::::::::::::::::::::
    ::
    ::	GIFEncoder (Encode the GIF)...
    ::
    */
    function __construct($GIF_src, $GIF_dly, $GIF_lop, $GIF_dis,$GIF_red, $GIF_grn, $GIF_blu, $GIF_ofs,$GIF_mod,$GIF_transparent=false)
    {
        if ( ! is_array ( $GIF_src ) && ! is_array ( $GIF_dly ) ) {
            printf	( "%s: %s", $this->VER, $this->ERR [ 'ERR00' ] );
            exit	( 0 );
        }
        if ( is_array ( $GIF_ofs ) && count ( $GIF_ofs ) > 1 ) {
            $this->SIG = 1;
            $this->OFS = $GIF_ofs;
        }
        $this->LOP = $GIF_lop === false ? false : (( $GIF_lop > -1 ) ? $GIF_lop : 0);
        $this->DIS = ( $GIF_dis > -1 ) ? ( ( $GIF_dis < 3 ) ? $GIF_dis : 3 ) : 2;
        if(!$GIF_transparent){
         $this->COL = ( $GIF_red > -1 && $GIF_grn > -1 && $GIF_blu > -1 ) ?
            ( $GIF_red | ( $GIF_grn << 8 ) | ( $GIF_blu << 16 ) ) : -1;
        }
        for ( $i = 0; $i < count ( $GIF_src ); $i++ ) {
            if ( strToLower ( $GIF_mod ) == "url" ) {
                $this->BUF [ ] = fread ( fopen ( $GIF_src [ $i ], "rb" ), filesize ( $GIF_src [ $i ] ) );
            }
            else if ( strToLower ( $GIF_mod ) == "bin" ) {
                $this->BUF [ ] = $GIF_src [ $i ];
            }
            else {
                printf	( "%s: %s ( %s )!", $this->VER, $this->ERR [ 'ERR02' ], $GIF_mod );
                exit	( 0 );
            }
            if ( substr ( $this->BUF [ $i ], 0, 6 ) != "GIF87a" && substr ( $this->BUF [ $i ], 0, 6 ) != "GIF89a" ) {
                printf	( "%s: %d %s", $this->VER, $i, $this->ERR [ 'ERR01' ] );
                exit	( 0 );
            }
            for ( $j = ( 13 + 3 * ( 2 << ( ord ( $this->BUF [ $i ] [10] ) & 0x07 ) ) ), $k = TRUE; $k; $j++ ) {
                switch ( $this->BUF [ $i ] [ $j ] ) {
                    case "!":
                        if ( ( substr ( $this->BUF [ $i ], ( $j + 3 ), 8 ) ) == "NETSCAPE" ) {
                            printf	( "%s: %s ( %s source )!", $this->VER, $this->ERR [ 'ERR03' ], ( $i + 1 ) );
                            exit	( 0 );
                        }
                        break;
                    case ";":
                        $k = FALSE;
                        break;
                }
            }
        }
        GIFEncoder::GIFAddHeader ( );
        for ( $i = 0; $i < count ( $this->BUF ); $i++ ) {
            GIFEncoder::GIFAddFrames ( $i, $GIF_dly [ $i ] );
        }
        GIFEncoder::GIFAddFooter ( );
    }
    /*
    :::::::::::::::::::::::::::::::::::::::::::::::::::
    ::
    ::	GIFAddHeader.(Add Header on Frame)..
    ::
    */
    function GIFAddHeader ( ) {
        $cmap = 0;
        if ( ord ( $this->BUF [ 0 ] [10] ) & 0x80 ) {
            $cmap = 3 * ( 2 << ( ord ( $this->BUF [ 0 ] [10] ) & 0x07 ) );
            $this->GIF .= substr ( $this->BUF [ 0 ], 6, 7		);
            $this->GIF .= substr ( $this->BUF [ 0 ], 13, $cmap	);
            if($this->LOP !== false)
            {
                $this->GIF .= "!\377\13NETSCAPE2.0\3\1" . GIFEncoder::GIFWord ( $this->LOP ) . "\0";
            }
        }
    }
    /*
    :::::::::::::::::::::::::::::::::::::::::::::::::::
    ::
    ::	GIFAddFrames (Add Frame on GIF)...
    ::
    */
    function GIFAddFrames ( $i, $d ) {
        $Locals_str = 13 + 3 * ( 2 << ( ord ( $this->BUF [ $i ] [10] ) & 0x07 ) );
        $Locals_end = strlen ( $this->BUF [ $i ] ) - $Locals_str - 1;
        $Locals_tmp = substr ( $this->BUF [ $i ], $Locals_str, $Locals_end );
        $Global_len = 2 << ( ord ( $this->BUF [ 0  ] [10] ) & 0x07 );
        $Locals_len = 2 << ( ord ( $this->BUF [ $i ] [10] ) & 0x07 );
        $Global_rgb = substr ( $this->BUF [ 0  ], 13,
            3 * ( 2 << ( ord ( $this->BUF [ 0  ] [10] ) & 0x07 ) ) );
        $Locals_rgb = substr ( $this->BUF [ $i ], 13,
            3 * ( 2 << ( ord ( $this->BUF [ $i ] [10] ) & 0x07 ) ) );
        $Locals_ext = "!\xF9\x04" . chr ( ( $this->DIS << 2 ) + 0 ) .
            chr ( ( $d >> 0 ) & 0xFF ) . chr ( ( $d >> 8 ) & 0xFF ) . "\x0\x0";
        if ( $this->COL > -1 && ord ( $this->BUF [ $i ] [10] ) & 0x80 ) {
            for ( $j = 0; $j < ( 2 << ( ord ( $this->BUF [ $i ] [10] ) & 0x07 ) ); $j++ ) {
                if	(
                    ord ( $Locals_rgb [ 3 * $j + 0 ] ) == ( ( $this->COL >> 16 ) & 0xFF ) &&
                    ord ( $Locals_rgb [ 3 * $j + 1 ] ) == ( ( $this->COL >>  8 ) & 0xFF ) &&
                    ord ( $Locals_rgb [ 3 * $j + 2 ] ) == ( ( $this->COL >>  0 ) & 0xFF )
                ) {
                    $Locals_ext = "!\xF9\x04" . chr ( ( $this->DIS << 2 ) + 1 ) .
                        chr ( ( $d >> 0 ) & 0xFF ) . chr ( ( $d >> 8 ) & 0xFF ) . chr ( $j ) . "\x0";
                    break;
                }
            }
        }
        switch ( $Locals_tmp [ 0 ] ) {
            case "!":
                $Locals_img = substr ( $Locals_tmp, 8, 10 );
                $Locals_tmp = substr ( $Locals_tmp, 18, strlen ( $Locals_tmp ) - 18 );
                break;
            case ",":
                $Locals_img = substr ( $Locals_tmp, 0, 10 );
                $Locals_tmp = substr ( $Locals_tmp, 10, strlen ( $Locals_tmp ) - 10 );
                break;
        }
        if ( ord ( $this->BUF [ $i ] [10] ) & 0x80 && $this->IMG > -1 ) {
            if ( $Global_len == $Locals_len ) {
                if ( GIFEncoder::GIFBlockCompare ( $Global_rgb, $Locals_rgb, $Global_len ) ) {
                    $this->GIF .= ( $Locals_ext . $Locals_img . $Locals_tmp );
                }
                else {
                    /*
                     *
                     * XY Padding...
                     *
                     */
                    if ( $this->SIG == 1 ) {
                        $Locals_img [ 1 ] = chr ( $this->OFS [ $i ] [ 0 ] & 0xFF );
                        $Locals_img [ 2 ] = chr ( ( $this->OFS [ $i ] [ 0 ] & 0xFF00 ) >> 8 );
                        $Locals_img [ 3 ] = chr ( $this->OFS [ $i ] [ 1 ] & 0xFF );
                        $Locals_img [ 4 ] = chr ( ( $this->OFS [ $i ] [ 1 ] & 0xFF00 ) >> 8 );
                    }
                    $byte  = ord ( $Locals_img [ 9 ] );
                    $byte |= 0x80;
                    $byte &= 0xF8;
                    $byte |= ( ord ( $this->BUF [ 0 ] [10] ) & 0x07 );
                    $Locals_img [ 9 ] = chr ( $byte );
                    $this->GIF .= ( $Locals_ext . $Locals_img . $Locals_rgb . $Locals_tmp );
                }
            }
            else {
                /*
                 *
                 * XY Padding...
                 *
                 */
                if ( $this->SIG == 1 ) {
                    $Locals_img [ 1 ] = chr ( $this->OFS [ $i ] [ 0 ] & 0xFF );
                    $Locals_img [ 2 ] = chr ( ( $$this->OFS [ $i ] [ 0 ] & 0xFF00 ) >> 8 );
                    $Locals_img [ 3 ] = chr ( $this->OFS [ $i ] [ 1 ] & 0xFF );
                    $Locals_img [ 4 ] = chr ( ( $this->OFS [ $i ] [ 1 ] & 0xFF00 ) >> 8 );
                }
                $byte  = ord ( $Locals_img [ 9 ] );
                $byte |= 0x80;
                $byte &= 0xF8;
                $byte |= ( ord ( $this->BUF [ $i ] [10] ) & 0x07 );
                $Locals_img [ 9 ] = chr ( $byte );
                $this->GIF .= ( $Locals_ext . $Locals_img . $Locals_rgb . $Locals_tmp );
            }
        }
        else {
            $this->GIF .= ( $Locals_ext . $Locals_img . $Locals_tmp );
        }
        $this->IMG  = 1;
    }
    /*
    :::::::::::::::::::::::::::::::::::::::::::::::::::
    ::
    ::	GIFAddFooter (Add footer in GIF)...
    ::
    */
    function GIFAddFooter ( ) {
        $this->GIF .= ";";
    }
    /*
    :::::::::::::::::::::::::::::::::::::::::::::::::::
    ::
    ::	GIFBlockCompare...
    ::
    */
    function GIFBlockCompare ( $GlobalBlock, $LocalBlock, $Len ) {
        for ( $i = 0; $i < $Len; $i++ ) {
            if	(
                $GlobalBlock [ 3 * $i + 0 ] != $LocalBlock [ 3 * $i + 0 ] ||
                $GlobalBlock [ 3 * $i + 1 ] != $LocalBlock [ 3 * $i + 1 ] ||
                $GlobalBlock [ 3 * $i + 2 ] != $LocalBlock [ 3 * $i + 2 ]
            ) {
                return ( 0 );
            }
        }
        return ( 1 );
    }
    /*
    :::::::::::::::::::::::::::::::::::::::::::::::::::
    ::
    ::	GIFWord (Create Text Word to Give)...
    ::
    */
    function GIFWord ( $int ) {
        return ( chr ( $int & 0xFF ) . chr ( ( $int >> 8 ) & 0xFF ) );
    }
    /*
    :::::::::::::::::::::::::::::::::::::::::::::::::::
    ::
    ::	GetAnimation.(Create Image to animation)..
    ::
    */
    function GetAnimation ( ) {
        return ( $this->GIF );
    }
}
class GIFGenerator {

    private $_defaultYPosition;
    private $_defaultXPosition;
    private $_defaultAngle;
    private $_defaultFont;
    private $_defaultFontColor;
    private $_defaultFontSize;
    private $_defaultDelay;
    private $_defaultRepeat;

    /**
     * Constructor of the GIFGenerator object which sets the default values
     *
     * @param array $kwargs default values to override
     */
    function __construct(array $kwargs = []) {

        // Set defaults
        $defaults = [
            "y-position" => 100,
            "x-position" => 100,
            "angle" => 0,
            "fonts" => 'fonts/Lato-Light.ttf',
            "fonts-color" => [255,255,255],
            "fonts-size" => 12,
            "delay" => 100,
            "transparent" => false,
            "repeat" => 0
        ];

        // Overwrite all the defaults with the arguments
        $args = array_merge($defaults,$kwargs);

        $this->_defaultYPosition = $args['y-position'];
        $this->_defaultXPosition = $args['x-position'];
        $this->_defaultAngle = $args['angle'];
        $this->_defaultFont = $args['fonts'];
        $this->_defaultFontColor = $args['fonts-color'];
        $this->_defaultFontSize = $args['fonts-size'];
        $this->_defaultDelay = $args['delay'];
        $this->_defaultRepeat = $args['repeat'];
        $this->_transparent = $args['transparent'];
    }

    /**
     * imagettftext with letter-spacing as extra feature
     *
     * @param  string  $image   	background image of the GIF
     * @param  integer $fontsize    fontsize of the text
     * @param  integer $angle   	rotation angle of the text
     * @param  integer $x       	x-position of the text inside the image
     * @param  integer $y       	y-position of the text inside the image
     * @param  string  $color   	text color
     * @param  integer $font    	font-family fo the text
     * @param  string  $text    	the actual text
     * @param  integer $spacing 	letter-spacing of the text
     * @return void
     */
    private function imagettftextSp($image, $fontsize, $angle, $x, $y, $color, $font, $text, $spacing = 0) {
        if ($spacing == 0) {
            $txt = imagettftext($image, $fontsize, $angle, $x, $y, $color, $font, $text);
        } else {
            $temp_x = $x;
            for ($i = 0; $i < strlen($text); $i++) {
                $txt = imagettftext($image, $fontsize, $angle, $temp_x, $y, $color, $font, $text[$i]);
                $temp_x += $spacing + ($txt[2] - $txt[0]);
            }
        }
    }

    /**
     * Generates the actual GIF image
     *
     * @param  array  	$array array with all image frames
     * @return resource        returns the actual GIF image
     */
    public function generate(array $array) {
        $frames = [];
        $frame_delay = [];

        foreach($array['frames'] as $frame) {

            $image = $this->_createImage($frame['image']);

            if(array_key_exists('text', $frame))
                foreach($frame['text'] as $key => $text) {

                    // Set defaults
                    $defaults = [
                        "angle" => $this->_defaultAngle,
                        "fonts" => $this->_defaultFont,
                        "fonts-color" => $this->_defaultFontColor,
                        "fonts-size" => $this->_defaultFontSize,
                        "y-position" => $this->_defaultYPosition,
                        "x-position" => $this->_defaultXPosition,
                        "text" => null,
                        "letter-spacing" => 0
                    ];

                    // Overwrite all the defaults with the arguments
                    $args = array_merge($defaults, $text);
                    $fontColor = is_array($args['fonts-color']) ? $args['fonts-color'] : $this->_hex2rgb($args['fonts-color']);
                    $text_color = imagecolorallocate($image, $fontColor[0], $fontColor[1], $fontColor[2]);

                    $this->imagettftextSp(
                        $image,
                        $args['fonts-size'],
                        $args['angle'],
                        $args['x-position'],
                        $args['y-position'],
                        $text_color,
                        $args['fonts'],
                        $args['text'],
                        $args['letter-spacing']);
                }

            $delay = (array_key_exists('delay', $frame)) ? $frame['delay'] : $this->_defaultDelay;

            ob_start();
            imagegif($image);
            $frames[]=ob_get_contents();
            $frame_delay[]=$delay; // Delay in the animation.
            ob_end_clean();
        }


        $repeat = (array_key_exists('repeat', $array)) ? $array['repeat'] : $this->_defaultRepeat;
        $gif = new GIFEncoder($frames,$frame_delay,$repeat,2,0,0,0,0,'bin', $this->_transparent);
        return $gif->GetAnimation();
    }

    /**
     * Creates an actual GIF image from the given source
     *
     * @param  string 	$imagePath path to the image
     * @return resource            returns the image
     */
    private function _createImage($imagePath) {
        $cImage = null;
        $tmp = explode('.', $imagePath);
        $ext = end($tmp);

        switch(strtolower($ext)){
            case 'jpg':
            case 'jpeg':
                $cImage = imagecreatefromjpeg($imagePath);
                break;
            case 'png':
                $cImage = imagecreatefrompng($imagePath);
                break;
        }

        return $cImage;
    }

    /**
     * Converts hexadecimal color string to an array with rgb values
     *
     * @param  string $hex the hexadecimal color which needs to be converted
     * @return array       returns an array with the rgb values
     */
    private function _hex2rgb($hex) {
        $hex = str_replace("#", "", $hex);

        if(strlen($hex) == 3) {
            $r = hexdec(substr($hex,0,1).substr($hex,0,1));
            $g = hexdec(substr($hex,1,1).substr($hex,1,1));
            $b = hexdec(substr($hex,2,1).substr($hex,2,1));
        } else {
            $r = hexdec(substr($hex,0,2));
            $g = hexdec(substr($hex,2,2));
            $b = hexdec(substr($hex,4,2));
        }
        $rgb = [$r, $g, $b];

        return $rgb;
    }
}
class EZGif {
    private $frames;
    private $headersSet = false;
    public function generateFromDir($directory, $delay = 100, array $filetypes = ['.png', '.jpg', '.jpeg', '.gif', '.tiff', '.bmp', '.ico']) {
        if (strcmp($directory, '/') != 0) {
            $directory = $directory . '/';
        }
        $gifframes = [];
        $frames = scandir($directory);
        foreach ($frames as $frame) {
            foreach($filetypes as $filetype) {
                if (strrpos($frame, $filetype) === strlen($frame) - strlen($filetype)) {
                    array_push($gifframes, ['image' => $directory . $frame, 'delay' => $delay]);
                }
            }
        }
        $imageFrames = [
            'repeat' => false,
            'transparent' => true,
            'frames' => $gifframes,
            'repeat' => true
        ];
        $this->frames = $imageFrames;
        return true;
    }
    public function setHeaders($disableCache = true, $setContentType = true) {
        if ($disableCache) {
            header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");
        }
        if ($setContentType) {
            header ('Content-type:image/gif');
            $this->headersSet = true;
        }
        if (!$disableCache && !$setContentType) {
            throw new Exception('No headers have been set by setHeaders! Delete setHeaders!');
        }
    }
    public function fileListGif(array $files, $delay = 100) {
        $gifframes = [];
        foreach ($files as $file) {
            array_push($gifframes, ['image' => $file, 'delay' => $delay]);
        }
        $imageFrames = [
            'repeat' => false,
            'transparent' => true,
            'frames' => $gifframes,
            'repeat' => true
        ];
        $this->frames = $imageFrames;
        return true;
    }
    public function displayGif() {
        if (!$this->headersSet) {
            throw new Exception('No headers have been set! Do not set headers manually!');
        } else {
            $gif = new GIFGenerator();
            echo $gif->generate($this->frames);
            exit;
        }
    }
    public function toFile($fileName) {
        $gif = new GIFGenerator();
        file_put_contents($fileName, $gif->generate($this->frames));
    }
}

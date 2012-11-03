<?php

/*


           -
         /   \
      /         \
   /   MINECRAFT   \
/         PHP         \
|\       CLIENT      /|
|.   \     2     /   .|
| ..     \   /     .. |
|    ..    |    ..    |
|       .. | ..       |
\          |          /
   \       |       /
      \    |    /
         \ | /
         
         
	by @shoghicp

			DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE
				Version 2, December 2004

Copyright (C) 2004 Sam Hocevar <sam@hocevar.net>

Everyone is permitted to copy and distribute verbatim or modified
copies of this license document, and changing it is allowed as long
as the name is changed.

			DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE
	TERMS AND CONDITIONS FOR COPYING, DISTRIBUTION AND MODIFICATION

	0. You just DO WHAT THE FUCK YOU WANT TO.


*/


//48
//1.4.3

$pstruct = array(
	"00" => array(
		"int",
	),
	
	"01" => array(
		"int",
		"string",
		"byte",
		"byte",
		"byte",
		"ubyte",
		"ubyte",
	),
	
	"02" => array(
		"byte",
		"string",
		"string",
		"int",
	),
	
	"03" => array(
		"string",
	),
	
	"04" => array(
		"long",
		"long",
	),	
	
	"05" => array(
		"int",
		"short",
		"slotData",
	),
	
	"06" => array(
		"int",
		"int",
		"int",
	),
	
	"07" => array(
		"int",
		"int",
		"bool",
	),
	
	"08" => array(
		"short",
		"short",
		"float",
	),
	
	"09" => array(
		"int",
		"byte",
		"byte",
		"short",
		"string",
	),
	
	"0a" => array(
		"bool",
	),
	
	"0b" => array(
		"double",
		"double",
		"double",
		"double",
		"bool",
	),

	"0c" => array(
		"float",
		"float",
		"bool",
	),
	
	"0d" => array(
		"double",
		"double",
		"double",
		"double",
		"float",
		"float",
		"bool",
	),
	
	"0e" => array(
		"byte",
		"int",
		"byte",
		"int",
		"byte",	
	),

	"0f" => array(
		"int",
		"ubyte",
		"int",
		"byte",
		"slotData",
		"byte",
		"byte",
		"byte",
	),
	
	"10" => array(
		"short",
	),
	
	"11" => array(
		"int",
		"byte",
		"int",
		"byte",
		"int",
	),
	
	"12" => array(
		"int",
		"byte",
	),
	
	"13" => array(
		"int",
		"byte",	
	),
	
	"14" => array(
		"int",
		"string",
		"int",
		"int",
		"int",
		"byte",
		"byte",
		"short",
		"entityMetadata",
	),
	
	"15" => array(
		"int",
		"dropArray",
		"int",
		"int",
		"int",
		"byte",
		"byte",
		"byte",
	),
	
	"16" => array(
		"int",
		"int",
	),
	
	"17" => array(
		"int",
		"byte",
		"int",
		"int",
		"int",
		"int", //if >0, fireball
		"short",
		"short",
		"short",
	),
	
	"18" => array(
		"int",
		"byte",
		"int",
		"int",
		"int",
		"byte",
		"byte",
		"byte",
		"short",
		"short",
		"short",
		"entityMetadata",
	),
	
	"19" => array(
		"int",
		"string",
		"int",
		"int",
		"int",
		"int",
	),
	
	"1a" => array(
		"int",
		"int",
		"int",
		"int",
		"short",
	),
	
	"1c" => array(
		"int",
		"short",
		"short",
		"short",
	),
	
	"1d" => array(
		"byte",
		"intArray",
	),
	
	"1e" => array(
		"int",
	),
	
	"1f" => array(
		"int",
		"byte",
		"byte",
		"byte",
	),
	
	"20" => array(
		"int",
		"byte",
		"byte",
	),
	
	"21" => array(
		"int",
		"byte",
		"byte",
		"byte",
		"byte",
		"byte",
	),
	
	"22" => array(
		"int",
		"int",
		"int",
		"int",
		"byte",
		"byte",
	),
	
	"23" => array(
		"int",
		"byte",
	),
	
	"26" => array(
		"int",
		"byte",
	),
	
	"27" => array(
		"int",
		"int",
	),
	
	"28" => array(
		"int",
		"entityMetadata",
	),
	
	"29" => array(
		"int",
		"byte",
		"byte",
		"short",
	),
	
	"2a" => array(
		"int",
		"byte",
	),
	
	"2b" => array(
		"float",
		"short",
		"short",
	),
	
	"33" => array(
		"int",
		"int",
		"bool",
		"ushort",
		"ushort",
		"int",
		"byteArray",
	),
	
	"34" => array(
		"int",
		"int",
		"short",
		"int",
		"newMultiblockArray",
	),
	
	"35" => array(
		"int",
		"byte",
		"int",
		"short",
		"byte",
	),
	
	"36" => array(
		"int",
		"short",
		"int",
		"byte",
		"byte",
		"short",
	),
	
	"37" => array(
		"int",
		"int",
		"int",
		"int",
		"byte",
	),
	
	"38" => array(
		"short",
		"int",
		"byteArray",
		"chunkInfo",
	),
	
	"3c" => array(
		"double",
		"double",
		"double",
		"float",
		"int",
		"explosionRecord",
		"float",
		"float",
		"float",
	),
	
	"3d" => array(
		"int",
		"int",
		"byte",
		"int",
		"int",
		"bool",
	),
	
	"3e" => array(
		"string",
		"int",
		"int",
		"int",
		"float",
		"byte",
	),
	
	"46" => array(
		"byte",
		"byte",
	),
	
	"47" => array(
		"int",
		"bool",
		"int",
		"int",
		"int",
	),
	
	"64" => array(
		"byte",
		"byte",
		"string",
		"byte",
	),
	
	"65" => array(
		"byte",
	),

	"66" => array(
		"byte",
		"short",
		"byte",
		"short",
		"byte",
		"slotData",
	),
	
	"67" => array(
		"byte",
		"short",
		"slotData",
	),
	
	"68" => array(
		"byte",
		"short",
		"slotArray",
	),
	
	"69" => array(
		"byte",
		"short",
		"short",
	),
	
	"6a" => array(
		"byte",
		"short",
		"bool",
	),
	
	"6b" => array(
		"short",
		"slotData",
	),
	
	"6c" => array(
		"byte",
		"byte",
	),
	
	"82" => array(
		"int",
		"short",
		"int",
		"string",
		"string",
		"string",
		"string",
	),
	
	"83" => array(
		"short",
		"short",
		"ubyte",
		"byteArray",
	),
	
	"84" => array(
		"int",
		"short",
		"int",
		"byte",
		"short",
		"byteArray",
	),
	
	"c8" => array(
		"int",
		"byte",
	),
	
	"c9" => array(
		"string",
		"byte",
		"short",
	),
	
	"ca" => array(
		"byte",
		"byte",
		"byte",
	),
	
	"cb" => array(
		"string",
	),
	
	"cc" => array(
		"string",
		"byte",
		"byte",
		"byte",
		"bool",
	),
	
	"cd" => array(
		"byte",
	),
	
	"fa" => array(
		"string",
		"short",
		"byteArray",
	),

	"fc" => array(
		"short",
		"byteArray",
		"short",
		"byteArray",	
	),
	
	"fd" => array(
		"string",
		"short",
		"byteArray",
		"short",
		"byteArray",	
	),
	
	"fe" => array(
		"byte",
	),
	
	"ff" => array(
		"string",	
	),
	
);


?>
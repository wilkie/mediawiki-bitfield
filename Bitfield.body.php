<?php

class Bitfield {
	public static function Setup(&$parser) {
		$parser->setFunctionHook('bitfield', 'Bitfield::Render');
		return true;
	}

	public static function Magic(&$magicWords, $langCode) {
# Add magic word
		$magicWords['bitfield'] = array(0, 'bitfield');
		return true;
	}

	public static function Render($parser) {
		// Render

		// Assume bitfield is read-writable
		$flags = 0;

		// Variadic foo
		$numargs = func_num_args()-1;
		$args = func_get_args();

		// First parameter is the bitwidth
		$bits = (int)func_get_arg(1);

		// Check that the first parameter is the bit width
		if ($bits == 0) {
			return "''Error: bits must be numerical and cannot be 0''";
		}

		$width = 550;
		$spacing = 5;
		$fieldwidth = 30;

		// Layout... |-| |-| |-| |-|
		//            ABBBB''''
		// Margin = $spacing
		// Cellwidth = $bits * $spacing + $fieldwidth
		// Totalwidth = $spacing + ($bits * $cellwidth)
		// $cellwidth = ($width - $spacing) / $bits

		$cellwidth = ($width - $spacing) / $bits;

		// Go through the rest to build the fields
		$arg_index = 2;
		while ($arg_index < count($args) && (int)$args[$arg_index] === 0) {
			// Found field flag
			$check = $args[$arg_index];
			if ($check === "readonly") {
				$flags |= 1;
			}
			else if ($check == "writeonly") {
				$flags |= 2;
			}

			$arg_index++;
		}

		// Header
		$output = '<div class="thumb"><table cellspacing="0" cellpadding="0" ';
		$output .= 'style="padding: 0; margin: 0; width: '.$width.'px">';

		$fieldsize = 0;
		$fieldflag = 0;
		$fieldname = 0;
		$fielddesc = 0;

		$fieldnames = array();

		$fieldsizes = array();
		$fieldflags = array();
		$descriptions = array();

		while($arg_index <= count($args)) {
			if ($arg_index == count($args)) {
				$checknum = 1;
			}
			else {
				$check = $args[$arg_index];
				$checknum = (int)$check;
			}
			if ($checknum === 0) {
				if ($fieldname === 0) {
					if ($check === "readonly") {
						$fieldflag |= 1;
					}
					else if ($check === "writeonly") {
						$fieldflag |= 2;
					}
					else if ($check === "reserved") {
						$fieldflag |= 4;
					}
					else {
						if ($fieldflag & 4) {
							$fieldname = "reserved_".$arg_index;
							$fielddesc = $check;
						}
						else {
							$fieldname = $check;
						}
					}
				}
				else if ($fielddesc === 0) {
					$fielddesc = $check;
				}
				else {
					return "''Error: expected a bit length for a field for argument $arg_index ("."$args[$arg_index]".")''";
				}
			}
			else {
				if ($fieldname == 0 && $fieldflag & 4) {
					$fieldname = "reserved_".$arg_index;
				}

				if ($fieldname !== 0) {
					$fieldnames[] = $fieldname;
					$fieldsizes[$fieldname] = $fieldsize;
					$fieldflags[$fieldname] = $fieldflag;
					if ($fielddesc !== 0) {
						$descriptions[$fieldname] = $fielddesc;
					}
				}

				$fieldsize = $checknum;
				$fieldflag = 0;
				$fieldname = 0;
				$fielddesc = 0;
			}
			$arg_index++;
		}

		$output .= '<tr style="padding: 0; margin: 0">';

		// Render fields
		$bit_index = $bits - 1;
		foreach($fieldnames as $i => $fieldname) {
			$fieldsize = $fieldsizes[$fieldname];
			$fieldflag = $fieldflags[$fieldname];

			$fieldwidth = $cellwidth * $fieldsize;

			if ($fieldsize == 1) {
				$output .= '<td colspan="2" style="text-align: center; font-size: 0.75em; padding-left: '.($spacing-2).'px; margin: 0; width: '.($fieldwidth).'px">'."\n";
				$output .= $bit_index;
				$output .= '</td>';
			}
			else {
				$output .= '<td style="font-size: 0.75em; padding-left: '.($spacing-2).'px; margin: 0; width: '.($fieldwidth/2).'px">'."\n";
				$output .= $bit_index;
				$output .= '</td>';
				$bit_index -= $fieldsize - 1;

				$output .= '<td style="text-align: right; font-size: 0.75em; padding: 0; margin: 0; width: '.($fieldwidth/2).'px">'."\n";
				$output .= $bit_index;
				$output .= '</td>';
			}

			$bit_index --;
		}

		$output .= "</tr>\n";
		$output .= '<tr style="padding: 0; margin: 0">';

		foreach($fieldnames as $i => $fieldname) {
			$fieldsize = $fieldsizes[$fieldname];
			$fieldflag = $fieldflags[$fieldname];

			$fieldwidth = $cellwidth * $fieldsize;
			$output .= '<td colspan="2" style="padding-bottom: '.($spacing-2).'px; padding-left: '.($spacing-2).'px; margin: 0; width: '.$fieldwidth.'px">';
			if ($fieldflag & 4) {
				// Reserved
				$output .= '<div class="thumbinner" style="background: #eee; color: #333">';
				if (isset($descriptions[$fieldname])) {
					$output .= $descriptions[$fieldname]."\n";
				}
				else {
					$output .= '&nbsp;'."\n";
				}
			}
			else {
				$output .= '<div class="thumbinner">'."\n";
				$output .= $fieldname;
			}
			$output .= '</div></td>';
		}

		$output .= "</tr>\n";

		// Footer
		$output .= "</table></div>\n";

		// Description Listing
		foreach($descriptions as $key => $value) {
			if (substr($key, 0, 9) !== "reserved_") {
				$output .= "* '''$key''' - $value \n";
			}
		}

		return $output;
	}
}

?>

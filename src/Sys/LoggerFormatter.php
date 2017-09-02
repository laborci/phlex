<?php namespace Phlex\Sys;


use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Formatter\OutputFormatterStyleInterface;


/**
 * Formatter style class for defining styles.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class LoggerFormatter implements OutputFormatterStyleInterface {

	const fg_default = '39';
	const fg_black = '30';
	const fg_red = '31';
	const fg_green = '32';
	const fg_yellow = '33';
	const fg_blue = '34';
	const fg_magenta = '35';
	const fg_cyan = '36';
	const fg_light_gray = '37';
	const fg_dark_gray = '90';
	const fg_light_red = '91';
	const fg_light_green = '92';
	const fg_light_yellow = '93';
	const fg_light_blue = '94';
	const fg_light_magenta = '95';
	const fg_light_cyan = '96';
	const fg_white = '97';

	const bg_default = '49';
	const bg_black = '40';
	const bg_red = '41';
	const bg_green = '42';
	const bg_yellow = '43';
	const bg_blue = '44';
	const bg_magenta = '45';
	const bg_cyan = '46';
	const bg_light_gray = '47';
	const bg_dark_gray = '100';
	const bg_light_red = '101';
	const bg_light_green = '102';
	const bg_light_yellow = '103';
	const bg_light_blue = '104';
	const bg_light_magenta = '105';
	const bg_light_cyan = '106';
	const bg_white = '107';

	private static $availableOptions = array(
		'bold'       => array('set' => 1, 'unset' => 22),
		'underscore' => array('set' => 4, 'unset' => 24),
		'blink'      => array('set' => 5, 'unset' => 25),
		'reverse'    => array('set' => 7, 'unset' => 27),
		'conceal'    => array('set' => 8, 'unset' => 28),
	);

	private $foreground;
	private $background;
	private $options = array();

	/**
	 * Initializes output formatter style.
	 *
	 * @param string|null $foreground The style foreground color name
	 * @param string|null $background The style background color name
	 * @param array       $options    The style options
	 */
	public function __construct($foreground = null, $background = null, array $options = array()) {
		if (null !== $foreground) {
			$this->setForeground($foreground);
		}
		if (null !== $background) {
			$this->setBackground($background);
		}
		if (count($options)) {
			$this->setOptions($options);
		}
	}

	/**
	 * Sets style foreground color.
	 *
	 * @param string|null $color The color name
	 *
	 * @throws InvalidArgumentException When the color name isn't defined
	 */
	public function setForeground($color = null) {
		if (null === $color) {
			$this->foreground = null;
			return;
		}

		$this->foreground = $color;
	}

	/**
	 * Sets style background color.
	 *
	 * @param string|null $color The color name
	 *
	 * @throws InvalidArgumentException When the color name isn't defined
	 */
	public function setBackground($color = null) {
		if (null === $color) {
			$this->background = null;
			return;
		}

		$this->background = $color;
	}

	/**
	 * Sets some specific style option.
	 *
	 * @param string $option The option name
	 *
	 * @throws InvalidArgumentException When the option name isn't defined
	 */
	public function setOption($option) {
		if (!isset(static::$availableOptions[$option])) {
			throw new InvalidArgumentException(sprintf(
				                                   'Invalid option specified: "%s". Expected one of (%s)',
				                                   $option,
				                                   implode(', ', array_keys(static::$availableOptions))
			                                   ));
		}

		if (!in_array(static::$availableOptions[$option], $this->options)) {
			$this->options[] = static::$availableOptions[$option];
		}
	}

	/**
	 * Unsets some specific style option.
	 *
	 * @param string $option The option name
	 *
	 * @throws InvalidArgumentException When the option name isn't defined
	 */
	public function unsetOption($option) {
		if (!isset(static::$availableOptions[$option])) {
			throw new InvalidArgumentException(sprintf(
				                                   'Invalid option specified: "%s". Expected one of (%s)',
				                                   $option,
				                                   implode(', ', array_keys(static::$availableOptions))
			                                   ));
		}

		$pos = array_search(static::$availableOptions[$option], $this->options);
		if (false !== $pos) {
			unset($this->options[$pos]);
		}
	}

	/**
	 * Sets multiple style options at once.
	 *
	 * @param array $options
	 */
	public function setOptions(array $options) {
		$this->options = array();

		foreach ($options as $option) {
			$this->setOption($option);
		}
	}

	/**
	 * Applies the style to a given text.
	 *
	 * @param string $text The text to style
	 *
	 * @return string
	 */
	public function apply($text) {
		$setCodes = array();
		$unsetCodes = array();

		if (null !== $this->foreground) {
			$setCodes[] = $this->foreground;
			$unsetCodes[] = 39;
		}
		if (null !== $this->background) {
			$setCodes[] = $this->background;
			$unsetCodes[] = 49;
		}
		if (count($this->options)) {
			foreach ($this->options as $option) {
				$setCodes[] = $option['set'];
				$unsetCodes[] = $option['unset'];
			}
		}

		if (0 === count($setCodes)) {
			return $text;
		}

		return sprintf("\033[%sm%s\033[%sm", implode(';', $setCodes), $text, implode(';', $unsetCodes));
	}
}

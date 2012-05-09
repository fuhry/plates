<?php

define('CONTROL_REQUIRED', 1 << 0);
// Attributes with this flag contribute to the overall rating
define('CONTROL_RATING', 1 << 1);
define('CONTROL_STRING_MULTILINE', 1 << 2);

function stars($rating, $count = 5)
{
	return '<span class="stars" style="width: ' . ($count * 16) . 'px" title="' . sprintf("%.1f/%d", $rating, $count) . '">' .
			'<span class="stars-inner" style="width: ' . ( round(($rating) * 16) ) . 'px"></span>' .
			'</span>';
}

$control_types = array();

abstract class Control {
	public $name;
	public $hint;
	public $size = 30;
	public $options = array();
	
	public function unserialize($value) {}
	public function serialize($value) {}
	public function validate($value) {}
	public function present($value) {}
	public function edit($name, $value = null) {}
	
	protected function present_generic($inner)
	{
		?>
		<tr>
			<td>
				<strong><?php echo htmlspecialchars($this->name); ?></strong>
				<?php if ( !empty($this->hint) ): ?>
				<br />
				<span class="help-inline"><?php echo htmlspecialchars($this->hint); ?></span>
				<?php endif; ?>
			</td>
			<td>
				<?php echo $inner; ?>
			</td>
		</tr>
		<?php
	}
	
	protected function edit_generic($inner)
	{
		?>
		<div class="control-group">
			<label class="control-label"><?php echo htmlspecialchars($this->name); ?></label>
			<div class="controls">
				<?php
				echo $inner;
				if ( !empty($this->hint) )
					printf('<p class="help-block">%s</p>', htmlspecialchars($this->hint));
				?>
			</div>
		</div>
		<?php
	}
}

final class Control_String extends Control {
	public function unserialize($value)
	{
		return $value;
	}
	
	public function serialize($value)
	{
		return $value;
	}
	public function validate($value)
	{
		return true;
	}
	public function present($value)
	{
		$this->present_generic(htmlspecialchars($value));
	}
	public function edit($name, $value = null)
	{
		$value = is_string($value) ? $value : '';
		$this->edit_generic("<input type=\"text\" name=\"" . htmlspecialchars($name) . "\" value=\"" . htmlspecialchars($value) . "\" />");
	}
	public function options()
	{
		?>
		<div class="control-group">
			<label class="control-label">Multi-line:</label>
			<div class="controls">
				<input type="checkbox" name="attr[plugin][String][a_flags][]" value="<?php echo strval(CONTROL_STRING_MULTILINE); ?>" />
			</div>
		</div>
		<?php
	}
}
$control_types[] = 'String';

final class Control_Rating extends Control {
	public $out_of = 5;
	
	public function unserialize($value)
	{
		return $value;
	}
	
	public function serialize($value)
	{
		return $value;
	}
	public function validate($value)
	{
		return is_float($value) || is_int($value) || (is_string($value) && preg_match('/^[0-9]+(\.[0-9]+)?$/', $value));
	}
	public function present($value)
	{
		$this->present_generic(stars($value, $this->out_of));
	}
	public function edit($name, $value = null)
	{
		$value = $this->validate($value) ? sprintf("%.1f", $value) : "0.0";
		$html = '<span class="stars editable" style="width: ' . ($this->out_of * 16) . 'px" data-size="' . $this->out_of . '"><span class="stars-inner"></span></span> <span class="stars-label"></span><input type="hidden" name="' . htmlspecialchars($name) . '" value="' . $value . '" />';
		/*
		if ( !empty($this->options['scale'][0]) )
			$html = htmlspecialchars($this->options['scale'][0]) . ' ' . $html;
		if ( !empty($this->options['scale'][1]) )
			$html = $html . ' ' . htmlspecialchars($this->options['scale'][1]);
		*/
		$this->edit_generic($html);
	}
	public function options()
	{
		?>
		<div class="control-group">
			<label class="control-label">Scale:</label>
			<div class="controls">
				<input type="text" name="attr[plugin][Rating][a_options][scale][]" value="" /> &mdash;
				<input type="text" name="attr[plugin][Rating][a_options][scale][]" value="" /><br />
				<span class="help-inline">Guide the user as to what a low or high rating indicates.</span>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label">Contribute to "overall rating":</label>
			<div class="controls">
				<input type="checkbox" name="attr[plugin][Rating][a_flags][]" value="<?php echo strval(CONTROL_RATING); ?>" />
			</div>
		</div>
		<div class="control-group">
			<label class="control-label">Number of stars:</label>
			<div class="controls">
				<input type="text" name="attr[plugin][Rating][a_size]" value="5" class="input-mini" />
			</div>
		</div>
		<?php
	}
}
$control_types[] = 'Rating';

final class Control_Radio extends Control {
	public function unserialize($value)
	{
		return $value;
	}
	
	public function serialize($value)
	{
		return $value;
	}
	public function validate($value)
	{
		return is_float($value) || is_int($value) || (is_string($value) && preg_match('/^[0-9]+(\.[0-9]+)?$/', $value));
	}
	public function present($value)
	{
		$this->present_generic(htmlspecialchars($value));
	}
	public function edit($name, $value = null)
	{
		$value = is_string($value) ? $value : '';
		$list = '';
		$i = 0;
		if ( empty($this->options['options']) )
			return;
		
		foreach ( $this->options['options'] as $opt )
		{
			$list .= '<label class="checkbox">';
			$checked = $value === $opt || ($i++ === 0 && empty($value)) ? ' checked="checked"' : '';
			$list .= '<input type="radio" name="' . htmlspecialchars($name) . '"' . $checked . ' value="' . htmlspecialchars($opt) . '" /> ';
			$list .= htmlspecialchars($opt);
			$list .= '</label>';
		}
		$this->edit_generic($list);
	}
	public function options()
	{
		?>
		<div class="control-group">
			<label class="control-label">Options:</label>
			<div class="controls">
				<input type="text" name="attr[plugin][Radio][a_options][options][]" value="" /><br />
				<br />
				<button class="btn radio-append">+ Add another</button>
			</div>
		</div>
		<?php
	}
}
$control_types[] = 'Radio';


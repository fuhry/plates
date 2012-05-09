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

abstract class Control {
	public $name;
	public $hint;
	public $size = 30;
	
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
				<input type="checkbox" name="flags[]" value="<?php echo strval(CONTROL_STRING_MULTILINE); ?>" />
			</div>
		</div>
		<?php
	}
}

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
		$this->edit_generic($html);
	}
	public function options()
	{
		?>
		<div class="control-group">
			<label class="control-label">Multi-line:</label>
			<div class="controls">
				<input type="checkbox" name="flags[]" value="<?php echo strval(CONTROL_STRING_MULTILINE); ?>" />
			</div>
		</div>
		<?php
	}
}


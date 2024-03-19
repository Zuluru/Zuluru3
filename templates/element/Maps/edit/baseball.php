<?php
/**
 * @var \App\View\AppView $this
 */
?>
<div class="sport_specific_fields" id="baseball_fields">
<p>Angle:
<span class="show_angle"></span>
<input type="submit" onclick="return updateAngle(10)" value="+10">
<input type="submit" onclick="return updateAngle(1)" value="+">
<input type="submit" onclick="return updateAngle(-1)" value="-">
<input type="submit" onclick="return updateAngle(-10)" value="-10">
</p>

<p>Base Paths:
<span class="show_width"></span>
<input type="submit" onclick="return updateWidth(1)" value="+">
<input type="submit" onclick="return updateWidth(-1)" value="-">
</p>

<p>Outfield:
<span class="show_length"></span>
<input type="submit" onclick="return updateLength(1)" value="+">
<input type="submit" onclick="return updateLength(-1)" value="-">
</p>
</div>

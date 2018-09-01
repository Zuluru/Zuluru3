<p>Angle:
<span class="show_angle"></span>
<input type="submit" onclick="return updateAngle(10)" value="+10">
<input type="submit" onclick="return updateAngle(1)" value="+">
<input type="submit" onclick="return updateAngle(-1)" value="-">
<input type="submit" onclick="return updateAngle(-10)" value="-10">
</p>

<p>Width:
<span class="show_width"></span>
<input type="submit" onclick="return updateWidth(1)" value="+">
<input type="submit" onclick="return updateWidth(-1)" value="-">
</p>

<p>Length:
<span class="show_length"></span>
<input type="submit" onclick="return updateLength(<?= 1 + $even_length ?>)" value="+">
<input type="submit" onclick="return updateLength(-<?= 1 + $even_length ?>)" value="-">
</p>

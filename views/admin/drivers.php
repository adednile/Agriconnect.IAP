<?php
session_start();
if (empty($_SESSION['is_admin'])) { echo 'Unauthorized'; exit; }
require_once __DIR__ . '/../../helpers/csrf.php';
$dModel = new \App\Models\Driver();
$drivers = $dModel->getAll();
?>
<h2>Manage Drivers</h2>
<form id="drv">
  <label>Name</label><input name="name"><label>Phone</label><input name="phone"><label>Vehicle</label><input name="vehicle">
  <input type="hidden" name="_csrf" value="<?php echo csrf_token(); ?>">
  <button type="button" onclick="createDrv()">Create</button>
</form>
<div id="res"></div>
<ul>
<?php foreach($drivers as $dr) echo '<li>'.htmlspecialchars($dr['name']).' — '.htmlspecialchars($dr['phone']).' — '.htmlspecialchars($dr['vehicle']).'</li>'; ?>
</ul>
<script>
function createDrv(){
  const f = document.getElementById('drv');
  fetch('../../routes/admin_drivers.php',{method:'POST',body:new FormData(f)}).then(r=>r.json()).then(js=>{ if(js.success) location.reload(); else document.getElementById('res').innerText = JSON.stringify(js); })
}
</script>

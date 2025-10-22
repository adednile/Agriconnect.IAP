<?php
// $product provided by MarketController::productDetail
session_start();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?php echo htmlspecialchars($product['name']); ?> - AgriConnect</title>
  <link rel="stylesheet" href="../../public/css/style.css">
  <style>.card{max-width:800px;margin:40px auto;background:white;padding:20px;border-radius:12px}</style>
</head>
<body>
  <div class="card">
    <a href="list.php">← Back to Market</a>
    <h2><?php echo htmlspecialchars($product['name']); ?></h2>
    <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
    <p class="price">Ksh <?php echo number_format($product['price'],2); ?></p>
    <p>Seller: <?php echo htmlspecialchars($product['farmer_name']); ?></p>

    <?php if (isset($_SESSION['user_id'])): ?>
      <?php require_once __DIR__ . '/../../helpers/csrf.php'; ?>
      <form id="orderForm">
        <label>Quantity</label>
        <input type="number" name="quantity" value="1" min="1" max="<?php echo (int)$product['quantity']; ?>">
        <label>Driver (optional)</label>
        <select name="driver_id">
          <option value="">No delivery</option>
          <?php
            $dModel = new \App\Models\Driver();
            $drivers = $dModel->getAll();
            foreach($drivers as $dr) echo '<option value="'.intval($dr['id']).'">'.htmlspecialchars($dr['name']).' — '.htmlspecialchars($dr['vehicle']).'</option>';
          ?>
        </select>
        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
        <input type="hidden" name="_csrf" value="<?php echo csrf_token(); ?>">
        <button type="button" onclick="createOrder()">Order & Checkout</button>
      </form>
      <div id="msg"></div>
      <script>
        function createOrder(){
          const form = document.getElementById('orderForm');
          const data = new FormData(form);
          fetch('../../routes/order_router.php', {method:'POST', body: data}).then(r=>r.json()).then(js=>{
            if(js.success){
              // simulate checkout - in real life we'd redirect to STK initiation
              document.getElementById('msg').innerText = 'Order created. Order ID: ' + js.order_id + ' — proceed to payment simulation.';
              if (js.sale_id) {
                const btn = document.getElementById('simulate-pay');
                btn.style.display = 'inline-block';
                btn.dataset.saleId = js.sale_id;
              }
            } else alert('Error: ' + (js.error || 'unknown'));
          });
        }
      </script>
      <button id="simulate-pay" style="display:none;margin-top:10px;" onclick="(function(){
        const sid = this.dataset.saleId; if (!sid) { alert('No sale id'); return; }
        const amt = (document.querySelector('input[name=quantity]').value * <?php echo (float)$product['price']; ?>);
        const fd = new FormData(); fd.append('sale_id', sid); fd.append('amount', amt); fd.append('mpesa_ref', 'SIM-'+Math.floor(Math.random()*1000000));
        fetch('../../routes/mpesa_callback.php',{method:'POST',body:fd}).then(r=>r.json()).then(js=>{ alert('Payment simulator: '+JSON.stringify(js)); }).catch(e=>alert('failed'));
      }).call(document.getElementById('simulate-pay'))">Simulate MPesa Payment</button>

      <?php require_once __DIR__ . '/../../helpers/csrf.php'; ?>
      <hr>
      <h3>Place a bid instead</h3>
      <form id="bidForm">
        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
        <input type="hidden" name="_csrf" value="<?php echo csrf_token(); ?>">
        <label>Offer Amount (Ksh)</label>
        <input type="number" name="amount" min="1" step="0.01" required>
        <label>Quantity</label>
        <input type="number" name="quantity" value="1" min="1" max="<?php echo (int)$product['quantity']; ?>">
        <button type="button" onclick="placeBid()">Place Bid</button>
      </form>
      <div id="bidMsg"></div>
      <script>
        function placeBid(){
          const f = document.getElementById('bidForm');
          const data = new FormData(f);
          fetch('../../routes/bid_create.php',{method:'POST',body:data}).then(r=>r.json()).then(js=>{
            if(js.success) document.getElementById('bidMsg').innerText = 'Bid placed';
            else alert('Bid failed: ' + (js.error||'unknown'));
          }).catch(()=>alert('Request failed'));
        }
      </script>
    <?php else: ?>
      <p><a href="../../public/index.php">Log in</a> to place an order.</p>
    <?php endif; ?>
  </div>
</body>
</html>

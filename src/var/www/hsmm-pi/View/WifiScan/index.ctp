<!-- File: /app/View/WifiScan/index.ctp -->

<div class="page-header">
  <h1>WiFi Scan</h1>
</div>

<div class="row">
  <div class="col-md-12">
    <?php
      if ($wifi_networks != NULL && sizeof($wifi_networks) > 0) {
    ?>
    <table class="table table-striped table-bordered">
      <tr>
        <th>ESSID</th>
        <th>MAC Address</th>
        <th>Channel</th>
        <th>Signal Quality</th>
      </tr>
      <?php
      foreach ($wifi_networks as $network) {
      ?>
      <tr>
        <td><?php echo $network['essid'];?></td>
        <td><?php echo $network['address'];?></td>
        <td><?php echo $network['channel'];?></td>
        <td>
          <div class="progress"><div class="progress-bar" role="progressbar" style="width: <?php echo round($network['signal_quality'] * 100) . '%';?>;"><?php echo round($network['signal_quality'] * 100) . '%';?></div>
          </div>
        </td>
      </tr>
      <?php
        }
      ?>
    </table>
    <?php
    }
    ?>
  </div>
</div>

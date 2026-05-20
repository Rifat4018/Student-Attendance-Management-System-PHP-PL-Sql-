<?php
include '../Includes/dbcon.php';

$tid = intval($_GET['tid']);

if($tid == 2){
    echo '<div class="form-group row mb-4 mt-3">
            <div class="col-xl-6">
                <label class="form-label-modern">Select Exact Date<span class="text-danger ml-1">*</span></label>
                <input type="date" class="form-control-modern" required name="singleDate">
            </div>
          </div>';
}
else if($tid == 3){
    echo '<div class="form-group row mb-4 mt-3">
            <div class="col-xl-6">
                <label class="form-label-modern">From Date<span class="text-danger ml-1">*</span></label>
                <input type="date" class="form-control-modern" required name="fromDate">
            </div>
            <div class="col-xl-6 mt-3 mt-xl-0">
                <label class="form-label-modern">To Date<span class="text-danger ml-1">*</span></label>
                <input type="date" class="form-control-modern" required name="toDate">
            </div>
          </div>';
}
else{
    // Type 1 is "All", so we don't need to generate any date inputs.
}
?>
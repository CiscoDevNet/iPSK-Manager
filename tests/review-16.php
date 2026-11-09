<?php
require __DIR__.'/review-bootstrap.php';
foreach ([0,1,100] as $count) verify(ipskQuotaAvailable($count,0),'Unlimited group unavailable');
verify(ipskQuotaAvailable(0,1) && !ipskQuotaAvailable(1,1),'Finite limit boundary');
verify(!ipskQuotaAvailable(false,0) && !ipskQuotaAvailable(0,-1),'Invalid count/limit accepted');
echo "PASS review 16: shared quota eligibility preserves unlimited and finite groups\n";

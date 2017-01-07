<?php
// takes JSON on stdin; writes clean report on stdout

$contents = '';

while (!feof(STDIN)) {
    $contents .= fread(STDIN, 8192);
}

// ---  process json data ----
$sgs = json_decode($contents, true);


foreach ($sgs['SecurityGroups'] as $g) {

	$name = $g['GroupName'];
	$sgid = $g['GroupId'];
	$ingressCount = count($g['IpPermissions']);
	$egressCount  = count($g['IpPermissionsEgress']);

	echo "$sgid ###### IN: $ingressCount OUT: $egressCount - $name\n";
	$rulesIngress = getRuleText($g['IpPermissions']);
	$rulesEgress  = getRuleText($g['IpPermissionsEgress']);

  // sort the rules for stability
  natsort($rulesIngress);
  natsort($rulesEgress);

	foreach ($rulesIngress as $in) {
		echo "$sgid <- $in\n";
	}

	foreach ($rulesEgress as $out) {
		echo "$sgid -> $out\n";
	}


}

// --------------------------------------------------------------------

function getRuleText($node) {
	if (! is_array($node)) {
		return "INVALID";
	}
	$rules = array();

	foreach ($node as $item) {
		switch($item['IpProtocol']) {
			case '6':   $proto = 'TCP'; break;
			case '17':  $proto = 'UDP'; break;
			case '-1':  $proto = 'ANY'; break;
			default:
				$proto = $item['IpProtocol'];
				break;
		}

		$nets = array();
		foreach ($item['IpRanges'] as $cidr) {
			$nets[] = $cidr['CidrIp'];
		}

		$ugids = array();
		foreach ($item['UserIdGroupPairs'] as $ugp) {
			$ugids[] = $ugp['GroupId'];
		}

    // merge the set of sources, and sort them for stability
    $sources = array_merge($nets, $ugids);
    natsort($sources);

		$ports = '';
		if (array_key_exists('FromPort', $item) &&
			array_key_exists('ToPort', $item)) {
			if ($item['ToPort'] == $item['FromPort']) {
				$ports = $item['FromPort'];
			} else {
				$ports = $item['FromPort'] .'-'. $item['ToPort'];
			}
		} elseif ('ANY' == $proto){
			$ports = 'ANY';
		}
    $sourceList = implode(' ', $sources);
		$rules[] = "$proto  $ports  $sourceList";
	}

	return $rules;
}

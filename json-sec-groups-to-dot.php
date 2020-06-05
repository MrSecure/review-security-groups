<?php
// takes JSON on stdin, writes dot notation on stdout

$contents = '';

while (!feof(STDIN)) {
    $contents .= fread(STDIN, 8192);
}

// ---  process json data ----
$sgs = json_decode($contents, true);

// var_dump($sgs);  exit;

echo <<< END_PREAMLLE
digraph SecurityGroups {
  "0.0.0.0/0" [ style="filled" fillcolor="grey" fontcolor="red" ];
END_PREAMLLE;

foreach ($sgs['SecurityGroups'] as $g) {
	$sgid = $g['GroupId'];
	$sgname= $g['GroupName'];

	$rulesIngress = getRuleDot($g['IpPermissions'], $sgid, 'in');
	$rulesEgress  = getRuleDot($g['IpPermissionsEgress'], $sgid, 'out');

	echo $rulesIngress;
	echo $rulesEgress;

	echo sprintf("  \"%s\" [ label = \"%s / %s\"];\n", $sgid, $sgname, $sgid);
}

echo "}\n";


// -------------------------------------------------------------------


function getRuleDot($node, $sg='unknown', $dir = 'in') {
	if (! is_array($node)) {
		return "";
	}
	$rules = '';

	foreach ($node as $item) {
		// Get the protocol
		switch($item['IpProtocol']) {
			case '6':   $proto = 'TCP'; break;
			case '17':  $proto = 'UDP'; break;
			case '-1':  $proto = 'ANY'; break;
			default:
				$proto = $item['IpProtocol'];
				break;
		}

		// Get the Port(s)
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


		// Generate a list of "other nodes"
		$nodes = array();
		foreach ($item['IpRanges'] as $cidr) {
			$nodes[] = $cidr['CidrIp'];

		}

		foreach ($item['UserIdGroupPairs'] as $ugp) {
			$nodes[] = $ugp['GroupId'];
		}


		// Generate an edge for each CIDR / SG with the ports as the label
		// $n -> $sg [ label ="$proto / $ports" ]
		// $extras = 'decorate=true ';
		$extras = '';
		foreach ($nodes as $n) {
			if ('out' == $dir) {
				$src = $sg;
				$dst = $n;
				$extras .= 'color="red" fontcolor="red"';
			} else {
				$dst = $sg;
				$src = $n;
			}
			$rules .= sprintf("  \"%s\" -> \"%s\" [ label = \"%s / %s\" %s];\n", $src, $dst, $proto, $ports, $extras);

		}


	}

	return $rules;
}

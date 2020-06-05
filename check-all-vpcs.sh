#!/bin/bash
WORK=$(pwd)
BASE=$(dirname "$0")
DATE=$(date -u "+%Y-%m-%d %T %Z")
FORMAT='png'

export LC_ALL=C

## Verify aws cli tool is ready, keys active
if ! aws --output json sts get-caller-identity > /dev/null ; then
  echo "Unable to query user data via AWS CLI tool; unable to continue."
  exit 1
fi

## List all VPCs, and grab network and name
aws ec2 describe-vpcs --output json | \
  jq -r '.Vpcs[] | .VpcId +" "+ .CidrBlock +" "+ (.Tags[] | select(.Key == "Name") | .Value)' > "${WORK}/vpcinfo.txt"
VPCS=$(cut -d' ' -f1 < "${WORK}/vpcinfo.txt" )

for v in $VPCS ; do {
	echo "$v";
  EXTRA=$(grep "$v" "${WORK}/vpcinfo.txt")
	# Collect SG info in json format
	aws ec2 describe-security-groups --filter "Name=vpc-id,Values=${v}" --output json > "${WORK}/${v}-sgs.json"

	# Generate a PNG drawing
	php "${BASE}/json-sec-groups-to-dot.php" < "${WORK}/${v}-sgs.json" | circo -T${FORMAT} -Glabel="Security Groups ${EXTRA} \n ${DATE}" -o "${WORK}/${v}.png"

	# Generate a short format repor
	php "${BASE}/review-security-groups.php" < "${WORK}/${v}-sgs.json" > "${WORK}/${v}-sgs.txt"
}
done

# Add capture timestamp to summary file
echo "### -------------- ${DATE}" > "${WORK}/all-security-groups.summary.txt"

aws ec2 describe-security-groups --output json > "${WORK}/all-security-groups.json"
php "${BASE}/review-security-groups.php" < "${WORK}/all-security-groups.json" | sort > "${WORK}/all-security-groups.txt"
php "${BASE}/json-sec-groups-to-dot.php" < "${WORK}/all-security-groups.json" > "${WORK}/all-security-groups.dot"
grep -e '###' "${WORK}/all-security-groups.txt" >> "${WORK}/all-security-groups.summary.txt"

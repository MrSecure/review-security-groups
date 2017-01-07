#!/bin/bash
WORK=$(pwd)
BASE=$(dirname $0)
DATE=$(date -u "+%Y-%m-%d %T %Z")
FORMAT='png'

export LC_ALL=C

## Verify aws cli tool is ready, keys active
aws --output json iam get-user > /dev/null
if [ $? -ne 0 ]; then
  echo "Unable to query user data via AWS CLI tool; unable to continue."
  exit 1
fi

## List all VPCs, and grab network and name
aws ec2 describe-vpcs --output text > ${WORK}/describe-vpcs.txt
cat ${WORK}/describe-vpcs.txt | awk ' /^VPCS/ {print $7,$2} /^TAGS.*Name/ {print $2,$3}' \
  | grep -B1 Name | tr "\n" "%" | sed s#vpc#@vpc#g | tr "@" "\n" | tr -d "%" | sed 's#Name# #g' \
  | grep 'vpc' | sed 's#--##g'  > ${WORK}/vpcinfo.txt

VPCS=$(cat ${WORK}/vpcinfo.txt | cut -d' ' -f1)

for v in $VPCS ; do {
	echo $v;
  EXTRA=$(grep $v ${WORK}/vpcinfo.txt)
	# Collect SG info in json format
	aws ec2 describe-security-groups --filter "Name=vpc-id,Values=${v}" --output json > ${WORK}/${v}-sgs.json

	# Generate a PNG drawing
	php ${BASE}/json-sec-groups-to-dot.php < ${WORK}/${v}-sgs.json | circo -T${FORMAT} -Glabel="Security Groups ${EXTRA} \n ${DATE}" -o ${WORK}/${v}.png

	# Generate a short format repor
	php ${BASE}/review-security-groups.php < ${WORK}/${v}-sgs.json > ${WORK}/${v}-sgs.txt
}
done

# Add capture timestamp to summary file
echo "### -------------- ${DATE}" > ${WORK}/all-security-groups.summary.txt

aws ec2 describe-security-groups --output json > ${WORK}/all-security-groups.json
php ${BASE}/review-security-groups.php < ${WORK}/all-security-groups.json | sort > ${WORK}/all-security-groups.txt
php ${BASE}/json-sec-groups-to-dot.php < ${WORK}/all-security-groups.json > ${WORK}/all-security-groups.dot
grep -e '###' ${WORK}/all-security-groups.txt >> ${WORK}/all-security-groups.summary.txt

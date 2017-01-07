# Security Group Review Tool

## Prerequisites

* AWS CLI
 * with keys setup
 * only your default account / region will be included 
 * http://docs.aws.amazon.com/cli/latest/userguide/installing.html

* GraphViz
 * http://graphviz.org/Download.php

* PHP 5.x should suffice


## Usage

* clone this repo somewhere
* cd to a directory where you want the data dumped
* call the 'check-all-vpcs.sh' script

```bash
mkdir /home/mrsecure/git/
cd /home/mrsecure/git
git clone https://github.com/MrSecure/review-security-groups.git
mkdir /home/mrsecure/data
cd /home/mrsecure/data
/home/mrsecure/git/review-security-groups/check-all-vpcs.sh
```



### Outputs

* File endings
 * json - raw data from aws cli
 * txt - greppable / parsable rules, one per line
 * dot - graphviz dot formatted file; nodes are SGs/CIDRs, edges are allowed proto/ports
 * png - circo output of dot file
 * summary.txt - security group id, # in & out rules, description

* File Groupings
 * vpc-XXXXXX - per-VPC data
 * all-security-groups.*  -  collection of all securiy groups accessible with the AWS keys used

```bash
[mrsecure@kirchhoff]$ /home/mrsecure/git/review-security-groups/check-all-vpcs.sh
vpc-12312312
vpc-abc4abc4
vpc-abcdabcd
vpc-0a0b0c0d
vpc-d7d7d7d7
vpc-dbdbdbdb
[mrsecure@kirchhoff]$ ls -l
total 9120
-rw-r--r--  1 mrsecure  hackers    23317 Jan 26 12:15 all-security-groups.dot
-rw-r--r--  1 mrsecure  hackers   191018 Jan 26 12:15 all-security-groups.json
-rw-r--r--  1 mrsecure  hackers     6046 Jan 26 12:15 all-security-groups.summary.txt
-rw-r--r--  1 mrsecure  hackers    18350 Jan 26 12:15 all-security-groups.txt
-rw-r--r--  1 mrsecure  hackers    66947 Jan 26 12:15 vpc-abc4abc4-sgs.json
-rw-r--r--  1 mrsecure  hackers     6258 Jan 26 12:15 vpc-abc4abc4-sgs.txt
-rw-r--r--  1 mrsecure  hackers  1298147 Jan 26 12:15 vpc-abc4abc4.png
-rw-r--r--  1 mrsecure  hackers    77278 Jan 26 12:15 vpc-0a0b0c0d-sgs.json
-rw-r--r--  1 mrsecure  hackers     7505 Jan 26 12:15 vpc-0a0b0c0d-sgs.txt
-rw-r--r--  1 mrsecure  hackers  2300886 Jan 26 12:15 vpc-0a0b0c0d.png
-rw-r--r--  1 mrsecure  hackers     3737 Jan 26 12:15 vpc-d7d7d7d7-sgs.json
-rw-r--r--  1 mrsecure  hackers      368 Jan 26 12:15 vpc-d7d7d7d7-sgs.txt
-rw-r--r--  1 mrsecure  hackers    44763 Jan 26 12:15 vpc-d7d7d7d7.png
-rw-r--r--  1 mrsecure  hackers     4199 Jan 26 12:15 vpc-12312312-sgs.json
-rw-r--r--  1 mrsecure  hackers      479 Jan 26 12:15 vpc-12312312-sgs.txt
-rw-r--r--  1 mrsecure  hackers    58184 Jan 26 12:15 vpc-12312312.png
-rw-r--r--  1 mrsecure  hackers    35296 Jan 26 12:15 vpc-dbdbdbdb-sgs.json
-rw-r--r--  1 mrsecure  hackers     3373 Jan 26 12:15 vpc-dbdbdbdb-sgs.txt
-rw-r--r--  1 mrsecure  hackers   430248 Jan 26 12:15 vpc-dbdbdbdb.png
-rw-r--r--  1 mrsecure  hackers     3721 Jan 26 12:15 vpc-abcdabcd-sgs.json
-rw-r--r--  1 mrsecure  hackers      367 Jan 26 12:15 vpc-abcdabcd-sgs.txt
-rw-r--r--  1 mrsecure  hackers    45070 Jan 26 12:15 vpc-abcdabcd.png
[mrsecure@kirchhoff]$
```

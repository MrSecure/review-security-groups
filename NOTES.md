# Other AWS CLI Commands

... to support gathering data for Top 20 CSCs

Note: collection is kept separate from analysis so that the collected data can be used for multiple analaysis attempts.


## Collect inventory information: Volumes, Instances, Security Groups, Network Interfaces, etc.

```
aws --output json ec2 describe-volumes > volumes.json
aws --output json ec2 describe-instances > instances.json
aws --output json ec2 describe-security-groups > sec-groups.json
aws --output json ec2 describe-network-interfaces > nics.json
aws --output json rds describe-db-instances > rds.json
aws --output json elb describe-load-balancers > elbs.json
```

## List Unencrypted volumes, and the instance they're attached to

```
cat volumes.json | jq -r '.Volumes[] | select(.Encrypted == false) | {Volume: .VolumeId, Type: .VolumeType, Encryption: .Encrypted, AttachedTo: .Attachments[].InstanceId }'
```

## List Unencrypted rds instances

```
cat rds.json | jq -r '.DBInstances[] | select(.StorageEncrypted == false) | {DBInstance: .DBInstanceIdentifier, Engine: .Engine, Encrypted: .StorageEncrypted}'
```

## List Instances - Launch time, Platform, Instance type & ID, security groups

```
cat instances.json | jq '.Reservations[].Instances[]  | [.LaunchTime, .Platform, .InstanceType, .InstanceId, .SecurityGroups[].GroupId]'
```

## List in-use interfaces and key details

```
cat nics.json | jq -C  '.NetworkInterfaces[] | select(.Status == "in-use") | {InterfaceId: .NetworkInterfaceId, AttachedTo: .Attachment.InstanceId, Owner: .Attachment.InstanceOwnerId,  IP: .PrivateIpAddress, SecurityGroups: .Groups[].GroupId } '
```


## Security Group Info


```
cat sec-groups.json | jq -C '.SecurityGroups[] | [.GroupId, .VpcId, .IpPermissions[].ToPort, .IpPermissions[].IpRanges[].CidrIp ]'
cat sec-groups.json | jq -C '.SecurityGroups[] | [.GroupId, .VpcId, .IpPermissions[].ToPort, .IpPermissions[].UserIdGroupPairs[].GroupId ]'
cat sec-groups.json | jq -C '.SecurityGroups[] | {gid: .GroupId,  port: .IpPermissions[].ToPort, srcgrps: .IpPermissions[].UserIdGroupPairs[].GroupId }'
```
## Sample VPC Flow Log Data Query

```
aws ec2 describe-flow-logs
aws logs filter-log-events --log-group-name <logGroupName>
```


## Create IP and DNS names to measure exposed surface

```
cat elbs.json | jq -M '.LoadBalancerDescriptions[] | { Type: .Scheme, Name: .DNSName} ' | tr '{:' '%,' | tr -d "\n}\"" | tr '%' "\n" | sort
cat instances.json | jq -M '.Reservations[].Instances[] | { ip_private: .PrivateIpAddress, ip_public: .PublicIpAddress }' | grep 'ip_' | grep -v null | tr -d '",' | sort
```


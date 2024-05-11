# WLC Configuration

[[README](README.md)] [[ISE Configuration](ISE.md)]

## Catalyst 9800 Controller Configuration
C9800 (Catalyst 9800) controller supports regular iPSK mode. There is no setting to enable iPSK on a policy profile aside from enabling AAA Override. NAC feature can be enabled for PSK assisted onborading. Following configuration snippet provides instructions on WLAN with iPSK enabled. The sample configures iPSK WLAN called IPSK-SSID with WLAN-ID of 1. This sample leverages default policy profile ' default-policy-profile'. If using non default profile, make sure to create tag mapping and apply it to the AP or AP list. This requires IOS-XE 16.10+.

```
C9800-CL(config)#wlan IPSK-SSID 1 IPSK-SSID
C9800-CL(config-wlan)#mac-filtering default
C9800-CL(config-wlan)#security wpa psk set-key ascii 0 Cisco123
C9800-CL(config-wlan)#no security wpa akm dot1x
C9800-CL(config-wlan)#security wpa akm psk
C9800-CL(config-wlan)#security dot1x authentication-list default
C9800-CL(config-wlan)#no shutdown
C9800-CL(config-wlan)#exit
C9800-CL(config)#wireless profile policy default-policy-profile
C9800-CL(config-wireless-policy)#shutdown
C9800-CL(config-wireless-policy)#aaa-override
C9800-CL(config-wireless-policy)#accounting-list default
C9800-CL(config-wireless-policy)#dhcp-tlv-caching
C9800-CL(config-wireless-policy)#http-tlv-caching
C9800-CL(config-wireless-policy)#nac
C9800-CL(config-wireless-policy)#radius-profiling
C9800-CL(config-wireless-policy)#vlan VLAN0080
C9800-CL(config-wireless-policy)#no shutdown
C9800-CL(config-wireless-policy)#exit
C9800-CL(config)#
```
In the case of IPSK assisted flow, create redirect ACL
```
C9800-CL(config)#ip access-list extended ACL_IPSK_REDIRECT
C9800-CL(config-ext-nacl)#10 deny udp any any
C9800-CL(config-ext-nacl)#20 permit tcp any any eq www
C9800-CL(config-ext-nacl)#30 permit tcp any any eq 443
C9800-CL(config-ext-nacl)#exit
C9800-CL(config)#
```
Note: In the case of Catalyst 9800, it is recommended to combine the redirect ACL with dACL such as following to limit access during redirected state. Create dACL with following ACE on ISE and apply it to the redirect authorization profile:
```
permit udp any host 192.168.201.71 eq domain
permit tcp any host 192.168.201.90 eq 8443
deny ip any any
```
To enable iPSK p2p blocking (Peer to peer blocking feature) with 17.1.1s+
```
C9800-CL(config)#wlan IPSK-SSID 1 IPSK-SSID
C9800-CL(config-wlan)#shutdown
C9800-CL(config-wlan)#peer-blocking allow-private-group
C9800-CL(config-wlan)#no shutdown
C9800-CL(config-wlan)#exit
```
For more information on Catalyst 9800 configuration please read [ISE and Catalyst 9800 Series Integration Guide](https://community.cisco.com/t5/security-documents/ise-and-catalyst-9800-series-integration-guide/ta-p/3753060)

# Meraki MR

For more information on Meraki IPSK, please read [Meraki IPSK with RADIUS Authentication](https://documentation.meraki.com/MR/Access_Control/IPSK_with_RADIUS_Authentication?_gl=1*mhgalx*_ga*MTY3OTQwNDc1LjE2OTEzNzY4MDQ.*_ga_KP8QEFW4ML*MTcxNTQwMDkyOS40My4xLjE3MTU0MDIyNTYuNDMuMC4w).


## AireOS Wireless Controller (Legacy)

The AireOS wireless controller supports regular iPSK mode as well as p2p blocking (Peer to peer blocking feature). There is no setting to enable iPSK on a PSK WLAN aside from enabling AAA Override. ISE-RADIUS (Or NAC-RADIUS) feature can be enabled for PSK assisted onboarding. The following configuration snippet provides instructions on WLAN with iPSK enabled. The sample configures an iPSK WLAN called IPSK-SSID with WLAN-ID of 1. This requires AireOS 8.5+.

```plaintext
(Cisco Controller) >config wlan create 1 IPSK-SSID IPSK-SSID
(Cisco Controller) >config wlan interface 1 ACCESS
(Cisco Controller) >config wlan mac-filtering enable 1
(Cisco Controller) >config wlan security wpa akm 802.1x disable 1
(Cisco Controller) >config wlan security wpa akm psk enable 1
(Cisco Controller) >config wlan security wpa akm psk set-key ascii Cisco123
(Cisco Controller) >config wlan aaa-override enable 1
(Cisco Controller) >config wlan nac radius enable 1
(Cisco Controller) >config wlan profiling radius all enable 1
(Cisco Controller) >config wlan enable 1
```

In the case of IPSK assisted flow, create redirect ACL
```plaintext
(Cisco Controller) >config acl create ACL_IPSK_REDIRECT
(Cisco Controller) >config acl rule add ACL_IPSK_REDIRECT 1
(Cisco Controller) >config acl rule action ACL_IPSK_REDIRECT 1 permit
(Cisco Controller) >config acl rule protocol ACL_IPSK_REDIRECT 1 6
(Cisco Controller) >config acl rule source port range ACL_IPSK_REDIRECT 1 0 65535
(Cisco Controller) >config acl rule destination address ACL_IPSK_REDIRECT 1 192.168.201.90 255.255.255.255
(Cisco Controller) >config acl rule destination port range ACL_IPSK_REDIRECT 1 8443 8443
(Cisco Controller) >config acl rule add ACL_IPSK_REDIRECT 1
(Cisco Controller) >config acl rule action ACL_IPSK_REDIRECT 1 permit
(Cisco Controller) >config acl rule protocol ACL_IPSK_REDIRECT 1 6
(Cisco Controller) >config acl rule source address ACL_IPSK_REDIRECT 1 192.168.201.90 255.255.255.255
(Cisco Controller) >config acl rule source port range ACL_IPSK_REDIRECT 1 8443 8443
(Cisco Controller) >config acl rule destination port range ACL_IPSK_REDIRECT 1 0 65535
(Cisco Controller) >config acl apply ACL_IPSK_REDIRECT
```
To enable iPSK p2p blocking (Peer to peer blocking feature) with AireOS version 8.8+
```plaintext
(Cisco Controller) >config wlan disable 1
(Cisco Controller) >config wlan peer-blocking allow-private-group 1
(Cisco Controller) >config wlan enable 1
```
For more information on AireOS WLC configuration please read AireOS WLC configuration for ISE


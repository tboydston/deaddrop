# Dead Drop

Dead drop is a simple web app that allows you to leave short messages for machines that are deleted immediatly after they are read. Below are a few possible use cases for this concept.  

- Kill switch for applications running behind a firewall. 
- Make quick config changes on the go. 
- Tell a firewalled server to open an SSH tunnel to a bridge server or close a tunnel. 

## Installation

Simply 'git clone' this repo into your web root. 

Next in the 'config.php' file add some users to $validCommanders array. 

```
$validCommanders = ['bob']
```

### Note on Security

This is not intended to be a highly secure messaging service. If you are using shared hosting or are behing a firewall or service that does URL tracking people with be able to see what links you use to send commands and could reproduce or abuse this. You can increase security by enabling .htpasswd in the .htaccess file. Currently this is commented out. 

## Usage

### Create Command

You can create a simple command or an associative command. 

Simple command

```
www.yoursite.com/[user]/set/[bot]/[command]
```

Response
```
{'command':[command]}
```

Associative Command

```
www.yoursite.com/[user]/set/[bot]/[key]/[content]
```

Response 
```
{[key]:[content]}
```


### Get Command

You can retrieve a command using this URL structure. Remember the command is immediately deleted once retrieved but history is still available in the logs.

```
www.yoursite.com/[user]/get/[bot]
```

Response 
```
{[key]:[content]}
```


### Get Log

Retrieving log of all commands. If there are many commands this could be quite large.

```
www.yoursite.com/[user]/log/[bot]
```

Response 
```
[{log entry 1},{log entry 2}, .ect ]
```

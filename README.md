
## V3 Upgrade instructions:
From the root directory of a V2 project, you can run the following commands to test out V3:

**Upgrade from V2 to V3:**
```
source <(curl -s https://calebporzio-public.s3.amazonaws.com/upgrade.sh)
```

**Update to latest V3:**
```
composer reinstall livewire/livewire
```

**Revert back to V2:**
```
source <(curl -s https://calebporzio-public.s3.amazonaws.com/revert.sh)
```


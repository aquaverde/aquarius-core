# Aquarius CMS

Aquarius3 is a content management system (CMS) developed by [aquaverde GmbH](http://aquaverde.ch).


# Licence

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

See COPYING for the full text ofthe licence. The licences of included
components are listed in doc/licence.txt



## Creating an installer pack

Many hosting providers do not have git installed or do not even offer shell access. In such cases you can upload an installer pack to the server. To generate such a pack, start from a aquarius installation, such as the cloned aquarius-blank repository as explained above.

#### 1. Change to the web root
```
cd /var/www
```

#### 2. Generate the installer pack

Installer packs may also be generated to perform upgrades of aquarius. In this case, you might want to use the 'bare' alias with the packer, so that site-specific templates and other files are not replaced.
```
aquarius/core/bin/packer bare
```

This will add all files the packer deems necessary. If you want to include still more files, you can add the paths as additional parameters, relative to the web-root.The packer script generates two files with a very long name. Example: aquarius_20120905-1537_v3.6.7-74-g8afa04b_all_52a0e.php aquarius_20120905-1537_v3.6.7-74-g8afa04b_all_52a0e.tar.bz
 

#### 3. Upload the pack-files to the web-root of your server. Example:
```
scp aquarius_20120905-1537_v3.6.7-74-g8afa04b_all_52a0e.* webmaster@aquarius-site.example:/var/www
```

#### 4. Run the generated installer script

http://aquarius-site.example/aquarius_20120905-1537_v3.6.7-74-g8afa04b_all_52a0e.php
It is best to remove the packs right after installation, for they allow setup operations without authentication!

 

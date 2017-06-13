# MAZHR

## Development

## Set up local environment

### Ubuntu

1. Install required PHP packages:

  ```
  sudo apt-get install php php-common php-mysql php-mcrypt php-curl
  ```

2. Install composer https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx
4. Go to `source/api` directory
3. Run `composer install` to install PHP dependencies
4. Configure your password and username in `source/api/app/config/local/database.php`, in the `mysql` section.
5. Go to `source/api/public`
6. Start the actual server:

  ```
  php -S localhost:8000
  ```



# - OUTDATED DOCS -

### Pre-requirements
- Ansible
- Vagrant (2.0+)
- VirtualBox

### Starting
```
# Clone repository
git clone ssh://git@git.corp.solinor.com/mazhr/mazhr.git
# or
git clone https://first.last@git.corp.solinor.com/scm/mazhr/mazhr.git

cd mazhr
vagrant up
vagrant ssh
```

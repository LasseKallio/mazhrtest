# MAZHR

## Development

## Set up local environment

### Ubuntu

1. Install required PHP packages:

  ```
  sudo apt-get install php php-common php-mysql php-mcrypt
  ```
2. Configure your password and username in `source/api/app/config/local/database.php`, in the `mysql` section.
3. Go to `public` directory with:

  ```
  cd source/api/public
  ```

4. Start the actual server:

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

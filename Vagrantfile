# -*- mode: ruby -*-
# vi: set ft=ruby :

# All Vagrant configuration is done below. The "2" in Vagrant.configure
# configures the configuration version (we support older styles for
# backwards compatibility). Please don't change it unless you know what
# you're doing.
Vagrant.configure(2) do |config|
  # The most common configuration options are documented and commented below.
  # For a complete reference, please see the online documentation at
  # https://docs.vagrantup.com.

  # Every Vagrant development environment requires a box. You can search for
  # boxes at https://atlas.hashicorp.com/search.
  config.vm.box = "centos/7"

  # Create a private network, which allows host-only access to the machine
  # using a specific IP.
  config.vm.network "private_network", ip: "10.0.0.10"

  config.vm.provider "virtualbox" do |vb, override|
      #override.ssh.private_key_path = "ansible/keys/vagrant.private.key"

      vb.name = "mazhr.dev"
      vb.memory = 1024
  end

  config.vm.provision "ansible" do |ansible|
      ansible.playbook = "ansible/development.yml"
      ansible.host_key_checking = false
  end
end

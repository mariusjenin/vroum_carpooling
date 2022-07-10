{ pkgs ? import <nixpkgs> {}
, unstable ? import <nixpkgs-unstable> {}
, lib ? pkgs.lib
}:

pkgs.mkShell {
  name = "vroum-dev-env";
  version = "0.0.1";

  buildInputs = with pkgs; [
    php73
    unstable.php73Packages.composer
  ];
}

If ($args[0] -Eq "all") {
    Write-Host("Uploading local directories to server using ./all.txt");
    sftp -b all.txt sauleilc@sauleil.com
}
If ($args[0] -Eq "site") {
    Write-Host("Uploading local resources to server using ./site.txt");
    sftp -b site.txt sauleilc@sauleil.com
}
else {
    Write-Host("Usage: update.ps1 [all, site]");
}

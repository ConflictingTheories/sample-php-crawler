foreach( $line in $(Get-Content .env)){
    $envData = $line.Split('=')
    [Environment]::SetEnvironmentVariable($envData[0], $envData[1], "User")   
}

docker-compose build
docker-compose up -d
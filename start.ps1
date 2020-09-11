foreach( $line in $(Get-Content .env)){
    $envData = $line.Split('=')
    [Environment]::SetEnvironmentVariable($envData[0], $envData[1], "User")   
}

$env:BUILD_PATH=$(Get-Location)

# Compose
docker-compose down -v
docker-compose build
docker-compose up -d
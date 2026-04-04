web: sh -c 'JAR=$(ls target/*.jar | grep -v "\.original" | head -n 1); if [ -z "$JAR" ]; then echo "No runnable jar found in target/"; exit 1; fi; java -Dserver.port=${PORT:-8080} -jar "$JAR"'

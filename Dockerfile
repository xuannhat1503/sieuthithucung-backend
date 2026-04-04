# syntax=docker/dockerfile:1

FROM maven:3.9.9-eclipse-temurin-21 AS build
WORKDIR /app

COPY pom.xml ./
COPY .mvn ./.mvn
COPY mvnw ./mvnw
COPY src ./src

RUN chmod +x ./mvnw && ./mvnw -DskipTests clean package

FROM eclipse-temurin:21-jre
WORKDIR /app

COPY --from=build /app/target/*.jar /app/app.jar

# Optional CA certificate for MySQL SSL verification
COPY ca.pem /app/certs/ca.pem
RUN sh -c 'if [ -f /app/certs/ca.pem ]; then keytool -importcert -alias mysql-ca -file /app/certs/ca.pem -keystore /app/certs/mysql-truststore.jks -storepass changeit -noprompt; fi'

ENV PORT=8080

ENTRYPOINT ["sh", "-c", "java -Dserver.port=${PORT} -jar /app/app.jar"]

package com.sieuthithucung.config;

import com.sieuthithucung.entity.RoleEntity;
import com.sieuthithucung.entity.UserEntity;
import com.sieuthithucung.repository.RoleRepository;
import com.sieuthithucung.repository.UserRepository;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.boot.CommandLineRunner;
import org.springframework.context.annotation.Bean;
import org.springframework.context.annotation.Configuration;

import java.time.LocalDateTime;

@Configuration
public class AdminBootstrapConfig {

    @Value("${app.admin.seed.enabled:false}")
    private boolean seedAdminEnabled;

    @Value("${app.admin.seed.email:}")
    private String seedAdminEmail;

    @Value("${app.admin.seed.password:}")
    private String seedAdminPassword;

    @Value("${app.admin.seed.name:STTC Admin}")
    private String seedAdminName;

    @Bean
    public CommandLineRunner bootstrapAdmin(RoleRepository roleRepository, UserRepository userRepository) {
        return args -> {
            if (!seedAdminEnabled || isBlank(seedAdminEmail) || isBlank(seedAdminPassword)) {
                return;
            }

            RoleEntity adminRole = roleRepository.findByNameIgnoreCase("ADMIN")
                    .orElseGet(() -> roleRepository.save(RoleEntity.builder()
                            .name("ADMIN")
                            .createdAt(LocalDateTime.now())
                            .updatedAt(LocalDateTime.now())
                            .build()));

            userRepository.findByEmail(seedAdminEmail.trim())
                    .orElseGet(() -> userRepository.save(UserEntity.builder()
                            .name(seedAdminName)
                            .email(seedAdminEmail.trim())
                            .password(seedAdminPassword)
                            .status("active")
                            .roleId(adminRole.getId())
                            .createdAt(LocalDateTime.now())
                            .updatedAt(LocalDateTime.now())
                            .build()));
        };
    }

    private boolean isBlank(String value) {
        return value == null || value.trim().isEmpty();
    }
}

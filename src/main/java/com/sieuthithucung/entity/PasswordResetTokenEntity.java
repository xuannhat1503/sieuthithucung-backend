package com.sieuthithucung.entity;

import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.Id;
import jakarta.persistence.Table;
import lombok.*;

import java.time.LocalDateTime;

@Entity
@Table(name = "password_reset_tokens")
@Getter
@Setter
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class PasswordResetTokenEntity {

    @Id
    @Column(nullable = false)
    private String email;

    @Column(nullable = false)
    private String token;

    @Column(name = "created_at")
    private LocalDateTime createdAt;
}


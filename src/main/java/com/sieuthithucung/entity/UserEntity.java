package com.sieuthithucung.entity;

import jakarta.persistence.*;
import lombok.*;

import java.time.LocalDateTime;

@Entity
@Table(name = "users")
@Getter
@Setter
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class UserEntity {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @Column(nullable = false)
    private String name;

    @Column(nullable = false, unique = true)
    private String email;

    @Column(nullable = false)
    private String password;

    private String status;

    @Column(name = "phone_number")
    private String phoneNumber;

    private String avatar;

    @Column(columnDefinition = "TEXT")
    private String address;

    @Column(name = "role_id", nullable = false)
    private Long roleId;

    @Column(name = "activation_token")
    private String activationToken;

    @Column(name = "google_id")
    private String googleId;

    @Column(name = "created_at")
    private LocalDateTime createdAt;

    @Column(name = "updated_at")
    private LocalDateTime updatedAt;
}


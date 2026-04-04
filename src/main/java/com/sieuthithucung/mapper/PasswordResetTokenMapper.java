package com.sieuthithucung.mapper;

import com.sieuthithucung.dto.PasswordResetTokenDto;
import com.sieuthithucung.entity.PasswordResetTokenEntity;

public class PasswordResetTokenMapper {
    public static PasswordResetTokenDto mapToPasswordResetTokenDto(PasswordResetTokenEntity entity) {
        return new PasswordResetTokenDto(
                entity.getEmail(),
                entity.getToken(),
                entity.getCreatedAt()
        );
    }

    public static PasswordResetTokenEntity mapToPasswordResetTokenEntity(PasswordResetTokenDto dto) {
        return new PasswordResetTokenEntity(
                dto.getEmail(),
                dto.getToken(),
                dto.getCreatedAt()
        );
    }
}
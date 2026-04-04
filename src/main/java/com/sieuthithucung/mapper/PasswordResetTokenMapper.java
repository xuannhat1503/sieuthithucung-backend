package com.sieuthithucung.mapper;

import com.sieuthithucung.dto.PasswordResetTokenDto;
import com.sieuthithucung.entity.PasswordResetTokenEntity;
import org.springframework.stereotype.Component;
import tools.jackson.databind.ObjectMapper;

@Component
public class PasswordResetTokenMapper extends BaseMapper<PasswordResetTokenEntity, PasswordResetTokenDto> {

    public PasswordResetTokenMapper(ObjectMapper objectMapper) {
        super(objectMapper, PasswordResetTokenEntity.class, PasswordResetTokenDto.class);
    }
}


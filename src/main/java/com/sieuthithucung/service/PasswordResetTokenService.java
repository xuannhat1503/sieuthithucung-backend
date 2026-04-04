package com.sieuthithucung.service;

import com.sieuthithucung.common.AbstractCrudService;
import com.sieuthithucung.dto.PasswordResetTokenDto;
import com.sieuthithucung.entity.PasswordResetTokenEntity;
import com.sieuthithucung.mapper.PasswordResetTokenMapper;
import com.sieuthithucung.repository.PasswordResetTokenRepository;
import org.springframework.stereotype.Service;

@Service
public class PasswordResetTokenService extends AbstractCrudService<PasswordResetTokenEntity, String, PasswordResetTokenDto> {

    public PasswordResetTokenService(PasswordResetTokenRepository repository, PasswordResetTokenMapper mapper) {
        super(repository, mapper, "Password reset token");
    }
}


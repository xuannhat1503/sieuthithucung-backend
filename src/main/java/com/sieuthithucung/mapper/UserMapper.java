package com.sieuthithucung.mapper;

import com.sieuthithucung.dto.UserDto;
import com.sieuthithucung.entity.UserEntity;

public class UserMapper {
    public static UserDto mapToUserDto(UserEntity entity) {
        return new UserDto(
                entity.getId(),
                entity.getName(),
                entity.getEmail(),
                entity.getPassword(),
                entity.getStatus(),
                entity.getPhoneNumber(),
                entity.getAvatar(),
                entity.getAddress(),
                entity.getRoleId(),
                entity.getActivationToken(),
                entity.getGoogleId(),
                entity.getCreatedAt(),
                entity.getUpdatedAt()
        );
    }

    public static UserEntity mapToUserEntity(UserDto dto) {
        return new UserEntity(
                dto.getId(),
                dto.getName(),
                dto.getEmail(),
                dto.getPassword(),
                dto.getStatus(),
                dto.getPhoneNumber(),
                dto.getAvatar(),
                dto.getAddress(),
                dto.getRoleId(),
                dto.getActivationToken(),
                dto.getGoogleId(),
                dto.getCreatedAt(),
                dto.getUpdatedAt()
        );
    }
}
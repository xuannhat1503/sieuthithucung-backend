package com.sieuthithucung.mapper;

import com.sieuthithucung.dto.ContactDto;
import com.sieuthithucung.entity.ContactEntity;

public class ContactMapper {
    public static ContactDto mapToContactDto(ContactEntity entity) {
        return new ContactDto(
                entity.getId(),
                entity.getFullName(),
                entity.getPhoneNumber(),
                entity.getEmail(),
                entity.getMessage(),
                entity.getIsReplied(),
                entity.getCreatedAt(),
                entity.getUpdatedAt()
        );
    }

    public static ContactEntity mapToContactEntity(ContactDto dto) {
        return new ContactEntity(
                dto.getId(),
                dto.getFullName(),
                dto.getPhoneNumber(),
                dto.getEmail(),
                dto.getMessage(),
                dto.getIsReplied(),
                dto.getCreatedAt(),
                dto.getUpdatedAt()
        );
    }
}
package com.sieuthithucung.mapper;

import com.sieuthithucung.dto.ContactDto;
import com.sieuthithucung.entity.ContactEntity;
import org.springframework.stereotype.Component;
import tools.jackson.databind.ObjectMapper;

@Component
public class ContactMapper extends BaseMapper<ContactEntity, ContactDto> {

    public ContactMapper(ObjectMapper objectMapper) {
        super(objectMapper, ContactEntity.class, ContactDto.class);
    }
}


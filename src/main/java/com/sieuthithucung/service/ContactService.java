package com.sieuthithucung.service;

import com.sieuthithucung.common.AbstractCrudService;
import com.sieuthithucung.dto.ContactDto;
import com.sieuthithucung.entity.ContactEntity;
import com.sieuthithucung.mapper.ContactMapper;
import com.sieuthithucung.repository.ContactRepository;
import org.springframework.stereotype.Service;

@Service
public class ContactService extends AbstractCrudService<ContactEntity, Long, ContactDto> {

    public ContactService(ContactRepository repository, ContactMapper mapper) {
        super(repository, mapper, "Contact");
    }
}


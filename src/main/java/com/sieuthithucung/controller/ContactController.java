package com.sieuthithucung.controller;

import com.sieuthithucung.common.AbstractCrudController;
import com.sieuthithucung.dto.ContactDto;
import com.sieuthithucung.service.ContactService;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequestMapping("/api/v1/contacts")
public class ContactController extends AbstractCrudController<Long, ContactDto> {

    public ContactController(ContactService service) {
        super(service);
    }
}


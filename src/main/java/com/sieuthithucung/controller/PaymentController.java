package com.sieuthithucung.controller;

import com.sieuthithucung.common.AbstractCrudController;
import com.sieuthithucung.dto.PaymentDto;
import com.sieuthithucung.service.PaymentService;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequestMapping("/api/v1/payments")
public class PaymentController extends AbstractCrudController<Long, PaymentDto> {

    public PaymentController(PaymentService service) {
        super(service);
    }
}


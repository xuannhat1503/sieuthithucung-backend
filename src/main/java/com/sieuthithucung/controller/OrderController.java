package com.sieuthithucung.controller;

import com.sieuthithucung.common.AbstractCrudController;
import com.sieuthithucung.dto.OrderDto;
import com.sieuthithucung.service.OrderService;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequestMapping("/api/v1/orders")
public class OrderController extends AbstractCrudController<Long, OrderDto> {

    public OrderController(OrderService service) {
        super(service);
    }
}


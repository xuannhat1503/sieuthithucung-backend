package com.sieuthithucung.service;

import com.sieuthithucung.common.AbstractCrudService;
import com.sieuthithucung.dto.PaymentDto;
import com.sieuthithucung.entity.PaymentEntity;
import com.sieuthithucung.mapper.PaymentMapper;
import com.sieuthithucung.repository.PaymentRepository;
import org.springframework.stereotype.Service;

@Service
public class PaymentService extends AbstractCrudService<PaymentEntity, Long, PaymentDto> {

    public PaymentService(PaymentRepository repository, PaymentMapper mapper) {
        super(repository, mapper, "Payment");
    }
}

